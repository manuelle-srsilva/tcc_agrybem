<?php

// Funções auxiliares para simular o ambiente do CartController e OrderController
if (!function_exists('jsonOk')) {
    function jsonOk($data = []){ return json_encode(array_merge(['success' => true], $data)); }
}

if (!function_exists('jsonErr')) {
    function jsonErr($msg = 'error') { return json_encode(['success' => false, 'message' => $msg]); }
}

?>
