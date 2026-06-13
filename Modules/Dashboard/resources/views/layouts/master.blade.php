<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyHub</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
            color: #333;
        }

        header {
            background-color: #1e1e2e;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        header h1 {
            font-size: 1.4rem;
            letter-spacing: 1px;
        }

        main {
            padding: 2rem;
        }
    </style>
</head>

<body>

    <header>
        <h1>MyHub</h1>
    </header>

    <main>
        {{ $slot }}
    </main>

</body>

</html>