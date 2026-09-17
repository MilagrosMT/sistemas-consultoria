<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inicio - Sistema Integrado Administrativo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-zinc-950 text-white">

    <!-- Barra superior -->
    <header class="border-b border-zinc-800 bg-zinc-950">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">

            <div>
                <h1 class="text-lg font-semibold">
                    Sistema Integrado Administrativo
                </h1>

                <p class="text-sm text-zinc-500">
                    Servicios administrativos y contables
                </p>
            </div>

            <a
                href="{{ route('login') }}"
                class="rounded-lg bg-teal-500 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-teal-600"
            >
                Iniciar sesión
            </a>

        </div>
    </header>


    <!-- Contenido principal -->
    <main>

        <!-- Presentación -->
        <section class="mx-auto max-w-7xl px-6 py-20">

            <div class="max-w-3xl">

                <span class="inline-flex rounded-full border border-teal-500/30 bg-teal-500/10 px-3 py-1 text-sm text-teal-400">
                    Gestión empresarial integrada
                </span>

                <h2 class="mt-6 text-4xl font-semibold tracking-tight md:text-5xl">
                    Centraliza la gestión administrativa y contable de tu empresa.
                </h2>

                <p class="mt-6 max-w-2xl text-lg leading-8 text-zinc-400">
                    Sistema orientado a pequeñas empresas de servicios de
                    consultoría contable, tributaria y administrativa,
                    permitiendo organizar la información y facilitar la gestión
                    de sus principales procesos.
                </p>

                <div class="mt-8 flex flex-wrap gap-4">

                    <a
                        href="{{ route('login') }}"
                        class="rounded-lg bg-teal-500 px-6 py-3 text-sm font-medium text-white transition hover:bg-teal-600"
                    >
                        Acceder al sistema
                    </a>

                    <a
                        href="#servicios"
                        class="rounded-lg border border-zinc-700 px-6 py-3 text-sm font-medium text-zinc-300 transition hover:border-zinc-500 hover:text-white"
                    >
                        Conocer el sistema
                    </a>

                </div>

            </div>

        </section>


        <!-- Servicios -->
        <section
            id="servicios"
            class="border-y border-zinc-800 bg-zinc-900/40"
        >

            <div class="mx-auto max-w-7xl px-6 py-16">

                <div class="max-w-2xl">

                    <h3 class="text-2xl font-semibold">
                        Servicios que gestiona la empresa
                    </h3>

                    <p class="mt-3 text-zinc-400">
                        El sistema permite organizar los servicios ofrecidos
                        a los clientes y dar seguimiento a su gestión.
                    </p>

                </div>


                <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-4">

                    <!-- Contabilidad -->
                    <div class="rounded-xl border border-zinc-800 bg-zinc-950 p-6">

                        <div class="mb-5 flex h-10 w-10 items-center justify-center rounded-lg bg-teal-500/10">

                            <svg
                                class="h-5 w-5 text-teal-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 14.25l6-6m-7.5 1.5h.008v.008H7.5V9.75zm9 4.5h.008v.008H16.5v-.008z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6.75 3.75h10.5A2.25 2.25 0 0119.5 6v12a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 18V6a2.25 2.25 0 012.25-2.25z"
                                />
                            </svg>

                        </div>

                        <h4 class="font-semibold">
                            Contabilidad
                        </h4>

                        <p class="mt-2 text-sm leading-6 text-zinc-400">
                            Gestión y organización de información contable
                            de los clientes.
                        </p>

                    </div>


                    <!-- Administración tributaria -->
                    <div class="rounded-xl border border-zinc-800 bg-zinc-950 p-6">

                        <div class="mb-5 flex h-10 w-10 items-center justify-center rounded-lg bg-teal-500/10">

                            <svg
                                class="h-5 w-5 text-teal-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 3l7.5 4.5v9L12 21l-7.5-4.5v-9L12 3z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M8.5 12h7M12 8.5v7"
                                />
                            </svg>

                        </div>

                        <h4 class="font-semibold">
                            Administración tributaria
                        </h4>

                        <p class="mt-2 text-sm leading-6 text-zinc-400">
                            Control de obligaciones tributarias, periodos,
                            vencimientos y estados.
                        </p>

                    </div>


                    <!-- Planillas -->
                    <div class="rounded-xl border border-zinc-800 bg-zinc-950 p-6">

                        <div class="mb-5 flex h-10 w-10 items-center justify-center rounded-lg bg-teal-500/10">

                            <svg
                                class="h-5 w-5 text-teal-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15 8.25h.008v.008H15V8.25zM15 11.25h.008v.008H15v-.008zM15 14.25h.008v.008H15v-.008z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 8.25h3M9 11.25h3M9 14.25h3M6.75 4.5h10.5A2.25 2.25 0 0119.5 6.75v10.5a2.25 2.25 0 01-2.25 2.25H6.75a2.25 2.25 0 01-2.25-2.25V6.75A2.25 2.25 0 016.75 4.5z"
                                />
                            </svg>

                        </div>

                        <h4 class="font-semibold">
                            Elaboración de planillas
                        </h4>

                        <p class="mt-2 text-sm leading-6 text-zinc-400">
                            Registro de empleados y gestión de remuneraciones
                            y planillas.
                        </p>

                    </div>


                    <!-- Reclutamiento -->
                    <div class="rounded-xl border border-zinc-800 bg-zinc-950 p-6">

                        <div class="mb-5 flex h-10 w-10 items-center justify-center rounded-lg bg-teal-500/10">

                            <svg
                                class="h-5 w-5 text-teal-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15 19.5a6 6 0 00-12 0M9 13.5a4 4 0 100-8 4 4 0 000 8z"
                                />
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M16.5 5.25a3 3 0 010 6M19.5 19.5a5.25 5.25 0 00-4.5-5.19"
                                />
                            </svg>

                        </div>

                        <h4 class="font-semibold">
                            Reclutamiento
                        </h4>

                        <p class="mt-2 text-sm leading-6 text-zinc-400">
                            Seguimiento de postulantes y etapas del proceso
                            de selección de personal.
                        </p>

                    </div>

                </div>

            </div>

        </section>


        <!-- Gestión integrada -->
        <section class="mx-auto max-w-7xl px-6 py-16">

            <div class="grid gap-10 lg:grid-cols-2 lg:items-center">

                <div>

                    <h3 class="text-2xl font-semibold">
                        Una gestión integrada en un solo sistema
                    </h3>

                    <p class="mt-4 leading-7 text-zinc-400">
                        La solución permite centralizar información que
                        anteriormente podía encontrarse distribuida entre
                        archivos Excel y comunicaciones por WhatsApp,
                        facilitando el seguimiento de las actividades
                        administrativas y contables.
                    </p>

                </div>


                <div class="grid grid-cols-2 gap-4">

                    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-5">
                        <p class="text-2xl font-semibold text-teal-400">
                            Clientes
                        </p>
                        <p class="mt-2 text-sm text-zinc-500">
                            Información centralizada
                        </p>
                    </div>

                    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-5">
                        <p class="text-2xl font-semibold text-teal-400">
                            Servicios
                        </p>
                        <p class="mt-2 text-sm text-zinc-500">
                            Catálogo y gestión
                        </p>
                    </div>

                    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-5">
                        <p class="text-2xl font-semibold text-teal-400">
                            Personal
                        </p>
                        <p class="mt-2 text-sm text-zinc-500">
                            Empleados y reclutamiento
                        </p>
                    </div>

                    <div class="rounded-xl border border-zinc-800 bg-zinc-900/50 p-5">
                        <p class="text-2xl font-semibold text-teal-400">
                            Control
                        </p>
                        <p class="mt-2 text-sm text-zinc-500">
                            Reportes e indicadores
                        </p>
                    </div>

                </div>

            </div>

        </section>


        <!-- Acceso -->
        <section class="border-t border-zinc-800">

            <div class="mx-auto max-w-7xl px-6 py-14">

                <div class="rounded-2xl border border-teal-500/20 bg-teal-500/5 p-8 md:p-10">

                    <h3 class="text-2xl font-semibold">
                        ¿Listo para ingresar?
                    </h3>

                    <p class="mt-3 max-w-2xl text-zinc-400">
                        Accede al sistema para gestionar los procesos
                        administrativos y contables de la organización.
                    </p>

                    <a
                        href="{{ route('login') }}"
                        class="mt-6 inline-flex rounded-lg bg-teal-500 px-6 py-3 text-sm font-medium text-white transition hover:bg-teal-600"
                    >
                        Iniciar sesión
                    </a>

                </div>

            </div>

        </section>

    </main>


    <!-- Pie de página -->
    <footer class="border-t border-zinc-800">

        <div class="mx-auto max-w-7xl px-6 py-6">

            <p class="text-center text-sm text-zinc-500">
                Sistema Integrado Administrativo y Contable
            </p>

        </div>

    </footer>

</body>
</html>