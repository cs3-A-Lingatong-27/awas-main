<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AWAS - Welcome</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white font-sans antialiased">
    <div class="w-full bg-[#1e00b8] p-4 flex items-center shadow-lg">
        <div class="flex items-center space-x-4">
            <img src="{{ asset('images/pshs-logo.png') }}" alt="PSHS Logo" class="h-16 w-auto border-2 border-cyan-400">
            <div class="text-white">
                <h1 class="font-bold text-xl uppercase leading-tight">Philippine Science High School</h1>
                <p class="text-sm text-cyan-300">Caraga Region Campus in Butuan City</p>
            </div>
        </div>
    </div>

    <div class="flex flex-col items-center justify-center min-h-[80vh]">
        <div class="bg-gray-300 p-10 rounded-[40px] shadow-md w-full max-w-md text-center">
            <h2 class="text-2xl font-medium text-gray-800 mb-8">Welcome to AWAS!</h2>
            
            <div class="flex flex-col space-y-4">
                <a href="{{ route('login') }}" 
                   class="bg-[#ffd712] hover:bg-yellow-400 text-black font-semibold py-3 px-6 rounded-full text-xl transition duration-200">
                    Log In
                </a>

                <a href="{{ route('register') }}" 
                   class="bg-[#ffd712] hover:bg-yellow-400 text-black font-semibold py-3 px-6 rounded-full text-xl transition duration-200">
                    Teacher/Admins registration
                </a>
            </div>
        </div>
    </div>
</body>
</html>