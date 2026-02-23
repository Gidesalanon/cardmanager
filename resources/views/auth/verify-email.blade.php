<x-guest-layout>
    <style>
        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            z-index: 9999;
            animation: slideInRight 0.5s ease-out;
            max-width: 400px;
        }
        
        .notification-toast .icon {
            width: 24px;
            height: 24px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .notification-toast.hiding {
            animation: slideOutRight 0.5s ease-out forwards;
        }
    </style>

    @if (session('success'))
        <div id="successNotification" class="notification-toast">
            <div class="icon">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="mb-4 text-xl text-gray-600 dark:text-gray-400">
        {{ __('Merci de vous être inscrit ! Avant de commencer, pourriez-vous vérifier votre adresse e-mail en cliquant sur le lien que nous venons de vous envoyer ? Si vous n\'avez pas reçu l\'e-mail, nous vous enverrons un autre avec plaisir.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-xl text-green-600 dark:text-green-400">
            {{ __('Un nouveau lien de vérification a été envoyé à l\'adresse e-mail que vous avez fournie lors de votre inscription.') }}    
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __("Renvoyer l'email de confirmation") }}
                </x-primary-button>
            </div>
        </form>

        {{-- <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                {{ __('Deconnecter') }}
            </button>
        </form> --}}
    </div>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const notification = document.getElementById('successNotification');
                if (notification) {
                    // Cacher la notification après 4 secondes
                    setTimeout(() => {
                        notification.classList.add('hiding');
                        setTimeout(() => {
                            notification.remove();
                        }, 500);
                    }, 4000);
                }
            });
        </script>
    @endif
</x-guest-layout>
