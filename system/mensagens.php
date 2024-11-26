<?php
function add_message($message, $type = 'info') {
    if (!isset($_SESSION['messages'])) {
        $_SESSION['messages'] = [];
    }
    $_SESSION['messages'][] = ['message' => $message, 'type' => $type, 'timestamp' => time()];
}

function display_messages() {
    $current_time = time();
    $display_time = 10; // Exibir toasts por 10 segundos
    $has_active_messages = false;

    if (isset($_SESSION['messages']) && !empty($_SESSION['messages'])) {
        foreach ($_SESSION['messages'] as $key => $message) {
            if ($current_time - $message['timestamp'] < $display_time) {
                echo '
                <div class="toast-container position-fixed bottom-0 end-0 p-3">
                    <div id="liveToast' . $key . '" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                            <img src="./includes/favicon_16.png" class="rounded me-2" alt="logo">
                            <strong class="me-auto">Aviso</strong>
                            <small>agora</small>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            ' . htmlspecialchars($message['message']) . '
                        </div>
                    </div>
                </div>';
                $has_active_messages = true;
            } else {
                unset($_SESSION['messages'][$key]);
            }
        }

        $_SESSION['messages'] = array_values($_SESSION['messages']);

        if ($has_active_messages) {
            echo '
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    setTimeout(function() {
                        var toasts = document.querySelectorAll(".toast");
                        toasts.forEach(function(toast) {
                            toast.classList.remove("show");
                        });
                    }, 10000);
                });
            </script>';
        }
    }
}
?>
