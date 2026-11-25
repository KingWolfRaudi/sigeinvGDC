<?php

/**
 * Gate de sincronía SBOM ↔ composer.lock (F6 / T3).
 *
 * Compara nombre y versión de cada paquete del lockfile con los componentes
 * del SBOM CycloneDX. Ignora metadatos (timestamps, referencia al commit,
 * versión de Composer) para que el resultado sea idéntico en local y en CI.
 *
 * Uso: php scripts/verify-sbom.php   (exit 0 = sincronizado, 1 = desviación)
 */
$root = dirname(__DIR__);

$lock = json_decode((string) @file_get_contents($root.'/composer.lock'), true);
$sbom = json_decode((string) @file_get_contents($root.'/docs/sbom/composer-sbom.json'), true);

if (! is_array($lock) || ! is_array($sbom)) {
    fwrite(STDERR, "No se pudo leer composer.lock o docs/sbom/composer-sbom.json\n");
    exit(1);
}

$lockMap = [];
foreach (array_merge($lock['packages'] ?? [], $lock['packages-dev'] ?? []) as $package) {
    $lockMap[$package['name']] = $package['version'];
}

$sbomMap = [];
foreach ($sbom['components'] ?? [] as $component) {
    $purl = (string) ($component['purl'] ?? '');

    if (! str_starts_with($purl, 'pkg:composer/')) {
        fwrite(STDERR, "PURL inesperado: {$purl}\n");
        exit(1);
    }

    [$name, $version] = array_pad(explode('@', substr($purl, strlen('pkg:composer/')), 2), 2, null);
    $sbomMap[$name] = $version;
}

$errors = [];

foreach ($lockMap as $name => $version) {
    if (! array_key_exists($name, $sbomMap)) {
        $errors[] = "en lock y ausente del SBOM: {$name}@{$version}";
    } elseif ($sbomMap[$name] !== $version) {
        $errors[] = "versión distinta: {$name} lock={$version} sbom={$sbomMap[$name]}";
    }
}

foreach ($sbomMap as $name => $version) {
    if (! array_key_exists($name, $lockMap)) {
        $errors[] = "en SBOM y ausente del lock: {$name}@{$version}";
    }
}

if ($errors !== []) {
    fwrite(STDERR, "SBOM desincronizado de composer.lock:\n - ".implode("\n - ", $errors)."\n");
    exit(1);
}

fwrite(STDOUT, 'SBOM sincronizado: '.count($lockMap)." paquetes 1:1 con composer.lock\n");
exit(0);
