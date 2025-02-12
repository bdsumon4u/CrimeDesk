import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Admin/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './resources/view/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
