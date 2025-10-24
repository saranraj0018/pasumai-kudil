<!DOCTYPE html>
<html lang="en">

<head>
    <x-partials.header />
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
        <!-- Toast Container -->
        <div id="toast-container" class="fixed top-5 right-5 space-y-2 z-50"></div>
    </div>


    <x-partials.scripts />
</body>

</html>
