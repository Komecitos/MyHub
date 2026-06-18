<?php

namespace Modules\FreeFire\Services;

use Illuminate\Support\Collection;

class FreefireWheelCalculator
{
    public const TOKEN_BASE_WEIGHT = [
        1 => 300,
        2 => 200,
        3 => 150,
        5 => 100,
        10 => 60,
        20 => 30,
        30 => 15,
        100 => 5,
    ];

    public const FADED_PRICES = [9, 19, 39, 69, 99, 199, 399, 799];

    public static function gcd(int $a, int $b): int
    {
        return $b === 0 ? $a : self::gcd($b, $a % $b);
    }

    public static function lcm(int $a, int $b): int
    {
        return (int) (($a * $b) / self::gcd($a, $b));
    }

    public static function calculate(Collection $slots): array
    {
        $tokenSlots = [];
        $itemRows = [];

        foreach ($slots as $slot) {
            if ($slot->type === 'token' && $slot->slot_count > 0) {
                $val = (int) $slot->token_value;
                $count = (int) $slot->slot_count;
                $baseWeight = self::TOKEN_BASE_WEIGHT[$val] ?? 0;
                $tokenSlots[] = [
                    'val' => $val,
                    'count' => $count,
                    'weight' => $baseWeight * $count,
                ];
            } elseif ($slot->type === 'item' && $slot->slot_count > 0) {
                $tokenReq = (int) ($slot->token_exchange ?? 0);
                if ($tokenReq > 0) {
                    $itemRows[] = [
                        'name' => $slot->item_name,
                        'token_req' => $tokenReq,
                        'slot' => (int) $slot->slot_count,
                    ];
                }
            }
        }

        $konstanta = 1;
        if (count($itemRows) > 0) {
            $konstanta = $itemRows[0]['token_req'];
            for ($i = 1; $i < count($itemRows); $i++) {
                $konstanta = self::lcm($konstanta, $itemRows[$i]['token_req']);
            }
        }

        foreach ($itemRows as &$item) {
            $item['base_weight'] = $konstanta / $item['token_req'];
            $item['total_weight'] = $item['base_weight'] * $item['slot'];
        }
        unset($item);

        $totalBobot = 0;
        foreach ($tokenSlots as $t) {
            $totalBobot += $t['weight'];
        }
        foreach ($itemRows as $i) {
            $totalBobot += $i['total_weight'];
        }

        $expectedToken = 0.0;
        if ($totalBobot > 0) {
            foreach ($tokenSlots as $t) {
                $expectedToken += ($t['weight'] / $totalBobot) * $t['val'];
            }
        }

        return [
            'total_bobot' => $totalBobot,
            'expected_token_per_spin' => round($expectedToken, 2),
            'token_slots' => $tokenSlots,
            'item_rows' => $itemRows,
        ];
    }

    public static function buildSessionEstimates($session): array
    {
        $wheelStats = self::calculate($session->slots);
        $expectedToken = $wheelStats['expected_token_per_spin'];
        $totalBobot = $wheelStats['total_bobot'];
        $currentToken = (int) $session->current_token;
        $currentSpin = (int) $session->current_spin;
        $startingToken = (int) ($session->starting_token ?? 0);

        $avgTokenPerSpin = $currentSpin > 0 ? ($currentToken - $startingToken) / $currentSpin : null;

        $luckActual = null;
        if ($currentSpin > 0 && $expectedToken > 0) {
            $ratio = $avgTokenPerSpin / $expectedToken;
            $luckActual = (int) round(min(100, max(0, $ratio * 50)));
        }

        $tokenRate = ($avgTokenPerSpin !== null && $avgTokenPerSpin > 0)
            ? $avgTokenPerSpin
            : ($expectedToken > 0 ? $expectedToken : 3);

        $items = [];
        foreach ($wheelStats['item_rows'] as $row) {
            $slot = $session->slots->first(function ($s) use ($row) {
                return $s->type === 'item'
                    && (int) $s->token_exchange === $row['token_req']
                    && strcasecmp(trim($s->item_name ?? ''), trim($row['name'] ?? '')) === 0;
            });

            $dropRate = $totalBobot > 0 ? ($row['total_weight'] / $totalBobot * 100) : 0;
            $remaining = max(0, $row['token_req'] - $currentToken);
            $theoreticalSpins = $expectedToken > 0 ? (int) ceil($row['token_req'] / $expectedToken) : 0;
            $estSpinsLeft = $remaining > 0 && $tokenRate > 0 ? (int) ceil($remaining / $tokenRate) : 0;

            $items[] = [
                'name' => $row['name'],
                'rarity' => $slot->rarity ?? 'epic',
                'drop_rate' => round($dropRate, 1),
                'token_exchange' => $row['token_req'],
                'remaining_token' => $remaining,
                'theoretical_spins' => $theoreticalSpins,
                'theoretical_diamond' => self::spinsToDiamond($theoreticalSpins),
                'est_spins_left' => $estSpinsLeft,
                'est_diamond_left' => self::spinsToDiamond($estSpinsLeft),
                'is_target' => strcasecmp(trim($row['name'] ?? ''), trim($session->item_name)) === 0,
            ];
        }

        usort($items, function ($a, $b) {
            if ($a['is_target'] !== $b['is_target']) {
                return $b['is_target'] <=> $a['is_target'];
            }

            return $a['token_exchange'] <=> $b['token_exchange'];
        });

        return [
            'expected_token_per_spin' => $expectedToken,
            'avg_token_per_spin' => $avgTokenPerSpin !== null ? round($avgTokenPerSpin, 2) : null,
            'luck_actual' => $luckActual,
            'items' => $items,
        ];
    }

    public static function resolveTargetToken($session): ?int
    {
        $match = $session->slots->first(function ($slot) use ($session) {
            return $slot->type === 'item'
                && $slot->token_exchange
                && strcasecmp(trim($slot->item_name ?? ''), trim($session->item_name)) === 0;
        });

        if ($match) {
            return (int) $match->token_exchange;
        }

        return $session->token_needed ? (int) $session->token_needed : null;
    }

    public static function spinsToDiamond(int $spins): int
    {
        $fiveSpins = intdiv($spins, 5);
        $oneSpins = $spins % 5;

        return ($fiveSpins * 39) + ($oneSpins * 9);
    }

    public static function fadedPrice(int $spinIndex, int $discountPercent): int
    {
        if ($spinIndex < 0 || $spinIndex >= count(self::FADED_PRICES)) {
            return 0;
        }

        return (int) ceil(self::FADED_PRICES[$spinIndex] * (1 - $discountPercent / 100));
    }

    public static function remainingFadedCost(int $currentSpin, int $discountPercent): int
    {
        $total = 0;
        for ($i = $currentSpin; $i < 8; $i++) {
            $total += self::fadedPrice($i, $discountPercent);
        }

        return $total;
    }
}
