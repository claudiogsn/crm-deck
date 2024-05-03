<?php

$config = parse_ini_file('../app/config/communication.ini');

$host = $config['host'];
$port = $config['port'];
$dbname = $config['name'];
$username = $config['user'];
$password = $config['pass'];


$dsn = "{$config['type']}:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500); 
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
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


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'));
    if (!json_decode(file_get_contents('php://input')))  {
        http_response_code(400);
        echo json_encode(array('message' => 'Nem chegou nada nao vei'));
        exit;
    } else {    
        $cpf = $data->cpf;  
    }
 

    if ($cpf) {
        if (validaCPF($cpf)) {
           
            $stmt = $pdo->prepare("SELECT * FROM cliente WHERE cpf = :cpf");
            $stmt->execute(['cpf' => $cpf]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                http_response_code(400); 
                echo json_encode(array('message' => 'CPF ja cadastrado'));
            } else {
                http_response_code(200); 
                echo json_encode(array('message' => 'CPF valido e disponível para cadastro'));
            }
        } else {
            http_response_code(400); 
            echo json_encode(array('message' => 'CPF invalido'));
        }
    } else {
        http_response_code(400); 
        echo json_encode(array('message' => 'CPF nao fornecido'));
    }
} else {
    http_response_code(405); 
    echo json_encode(array('message' => 'Metodo de requisicao invalido'));
}


header('Content-Type: application/json');
