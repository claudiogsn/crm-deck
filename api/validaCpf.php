<?php

require_once 'config.php';
require_once 'functions.php';




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
