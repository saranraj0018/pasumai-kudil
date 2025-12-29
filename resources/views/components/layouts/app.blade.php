<!DOCTYPE html>
<html lang="en">

<head>
    <x-partials.header />
    <style>
        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini bg-gray-100">
    <div class="wrapper">
        <div class="flex">

            <aside class="fixed top-0 left-0 h-full w-64 shadow-lg">
                <x-partials.sidebar />
            </aside>

            <div class="flex flex-col flex-1 ml-64">
                <div class="flex justify-start items-start">
                    <div class="flex flex-col w-full p-2">
                        <x-partials.navbar />
                        <div class="content-wrapper p-6">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="globalLoader" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center">
            <div
                class="max-w-sm p-6 bg-neutral-primary-soft border border-default rounded-base shadow-xs relative text-center">
                <!-- Loading SVG -->
                <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>

                <h5 class="mt-4 text-xl font-semibold text-heading opacity-80">
                    Loading...
                </h5>
            </div>
        </div>


        <!-- Toast Container -->
        <div id="toast-container" class="fixed top-5 right-5 space-y-2 z-50"></div>
    </div>


    <x-partials.scripts />
</body>

</html>
