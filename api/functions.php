<?php

require_once 'config.php';

function validateData($data) {
    return true;
}

function registerClient($data) {
    global $pdo;
    
    // Verifica se o CPF já existe no banco de dados
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM cliente WHERE cpf = ?");
    $stmt->execute([$data['cpf']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row['count'] > 0) {
        http_response_code(400);
        return array("error" => "CPF ja cadastrado");
    }
    
    // Insira os dados do cliente na tabela cliente
    $stmt = $pdo->prepare("INSERT INTO cliente (nome, email, telefone, cpf, data_nascimento) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$data['nome'], $data['email'], $data['telefone'], $data['cpf'], $data['data_nascimento']]);
    
    // Gera o código do voucher
    $voucherCode = generateVoucherCode();

    // Verifica se o código do voucher já existe no banco de dados
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM voucher WHERE codigo = ?");
    $stmt->execute([$voucherCode]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row['count'] > 0) {
        // Se o código do voucher já existir, gera outro código
        $voucherCode = generateVoucherCode();
    }
    
    // Insere o voucher na tabela voucher
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("INSERT INTO voucher (cpf_cliente, campanha_id, codigo, data_criacao, ip_criacao) VALUES (?, ?, ?, NOW(), ?)");
    $stmt->execute([$data['cpf'], $data['campanha_id'], $voucherCode, $ip]);
    
    return array("voucher_code" => $voucherCode);
}


function generateVoucherCode() {
    // Implementa a lógica para gerar o código do voucher (3 letras e 3 números)
    $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $numbers = '0123456789';
    $voucherCode = '';
    for ($i = 0; $i < 3; $i++) {
        $voucherCode .= $letters[rand(0, strlen($letters) - 1)];
    }
    for ($i = 0; $i < 3; $i++) {
        $voucherCode .= $numbers[rand(0, strlen($numbers) - 1)];
    }
    return $voucherCode;
}

function validaCPF($cpf) {

    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11) {
        return false;
    }

    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    // Calcular os dígitos verificadores
    for ($i = 9; $i < 11; $i++) {
        $sum = 0;
        for ($j = 0; $j < $i; $j++) {
            $sum += $cpf[$j] * (($i + 1) - $j);
        }
        $digit = ((10 * $sum) % 11) % 10;
        if ($cpf[$i] != $digit) {
            return false;
        }
    }
    return true;
}

