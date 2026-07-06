<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Tukarkuy</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .glitch {
            animation: glitch 1s linear infinite;
        }
        @keyframes glitch {
            2%, 64% {
                transform: translate(2px, 0) skew(0deg);
            }
            4%, 60% {
                transform: translate(-2px, 0) skew(0deg);
            }
            62% {
                transform: translate(0, 0) skew(5deg);
            }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 text-white">
    
    <div class="text-center max-w-2xl">
        
        <!-- 404 Icon -->
        <div class="mb-8 floating">
            <div class="inline-block relative">
                <i class="fas fa-box-open text-9xl text-purple-500/30"></i>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                    <i class="fas fa-question text-5xl text-purple-400"></i>
                </div>
            </div>
        </div>

        <!-- 404 Text -->
        <h1 class="text-8xl font-black mb-4 bg-gradient-to-r from-purple-400 via-pink-400 to-blue-400 bg-clip-text text-transparent glitch">
            404
        </h1>
        
        <h2 class="text-3xl font-bold mb-4">Oops! Page Not Found</h2>
        
        <p class="text-gray-400 text-lg mb-8 max-w-md mx-auto">
            The page you're looking for seems to have taken a wrong turn. Don't worry, even the best tracking systems sometimes lose their way.
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
            <a href="/" class="inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 rounded-xl font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-purple-500/50">
                <i class="fas fa-home mr-2"></i>
                Go Home
            </a>
            <button onclick="history.back()" class="inline-flex items-center justify-center px-8 py-4 bg-slate-700/80 hover:bg-slate-600 rounded-xl font-semibold transition-all duration-200 border border-slate-600">
                <i class="fas fa-arrow-left mr-2"></i>
                Go Back
            </button>
        </div>

        <!-- Suggestions -->
        <div class="bg-slate-800/50 backdrop-blur-sm rounded-2xl p-6 border border-slate-700">
            <h3 class="font-semibold mb-4 text-left flex items-center">
                <i class="fas fa-lightbulb text-yellow-400 mr-2"></i>
                Helpful Links
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-left">
                <a href="/track" class="flex items-center p-3 rounded-lg hover:bg-slate-700/50 transition group">
                    <i class="fas fa-search text-purple-400 mr-3 group-hover:scale-110 transition"></i>
                    <span class="text-sm">Track Shipments</span>
                </a>
                <a href="/tickets" class="flex items-center p-3 rounded-lg hover:bg-slate-700/50 transition group">
                    <i class="fas fa-ticket text-blue-400 mr-3 group-hover:scale-110 transition"></i>
                    <span class="text-sm">Buy Credits</span>
                </a>
                <a href="/history" class="flex items-center p-3 rounded-lg hover:bg-slate-700/50 transition group">
                    <i class="fas fa-history text-green-400 mr-3 group-hover:scale-110 transition"></i>
                    <span class="text-sm">Reveal History</span>
                </a>
                <a href="/settings" class="flex items-center p-3 rounded-lg hover:bg-slate-700/50 transition group">
                    <i class="fas fa-cog text-gray-400 mr-3 group-hover:scale-110 transition"></i>
                    <span class="text-sm">Settings</span>
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-sm text-gray-500">
            <p>Need help? <a href="mailto:support@tukaruy.online" class="text-purple-400 hover:text-purple-300 underline">Contact Support</a></p>
        </div>

    </div>

    <script>
        // Add particle effect on mouse move
        document.addEventListener('mousemove', (e) => {
            const particle = document.createElement('div');
            particle.style.position = 'fixed';
            particle.style.left = e.clientX + 'px';
            particle.style.top = e.clientY + 'px';
            particle.style.width = '4px';
            particle.style.height = '4px';
            particle.style.background = 'rgba(139, 92, 246, 0.5)';
            particle.style.borderRadius = '50%';
            particle.style.pointerEvents = 'none';
            particle.style.animation = 'fadeOut 1s ease-out forwards';
            document.body.appendChild(particle);
            
            setTimeout(() => particle.remove(), 1000);
        });
        
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                to {
                    transform: translateY(-20px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>

</body>
</html>
