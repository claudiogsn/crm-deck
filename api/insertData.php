<?php

require_once 'functions.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents("php://input"), true);

    if (validateData($data)) {
        $response = registerClient($data);
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Dados inválidos."));
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Método não permitido."));
}
