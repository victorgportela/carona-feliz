<?php

// Script de teste para verificar se o Intervention Image está funcionando
require_once __DIR__ . '/vendor/autoload.php';

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

echo "Testando Intervention Image...\n";

try {
    // Criar um manager com driver GD
    $manager = new ImageManager(new Driver());
    echo "✅ ImageManager criado com sucesso\n";
    
    // Criar uma imagem simples para teste
    $image = $manager->create(100, 100)->fill('red');
    echo "✅ Imagem criada com sucesso\n";
    
    // Redimensionar
    $resized = $image->scaleDown(50, 50);
    echo "✅ Redimensionamento funcionou\n";
    
    // Encoding com método específico
    $encoded = (string) $resized->toJpeg(85);
    echo "✅ Encoding funcionou\n";
    
    echo "Teste concluído com sucesso!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
} 