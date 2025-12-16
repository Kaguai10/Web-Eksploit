<?php
function render($template, $data = []) {
    extract($data);
    include __DIR__ . "/templates/$template.php";
}

function page_main() {
    $encoded_output = '';
    $decoded_output = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_POST['do_encode'])) {
            $plain = $_POST['encode_text'] ?? '';
            $cmd = "echo $plain | base64 2>&1";
            $encoded_output = shell_exec($cmd);
        }

        if (isset($_POST['do_decode'])) {
            $b64 = $_POST['decode_text'] ?? '';
            $cmd = "echo $b64 | base64 -d 2>&1";
            $decoded_output = shell_exec($cmd);
        }
    }

    render('header', ['title' => 'Base64 Tool']);
    render('main', [
        'enc' => $encoded_output,
        'dec' => $decoded_output
    ]);
    render('footer');
}

page_main();
