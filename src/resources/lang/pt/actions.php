<?php

return [
    'cache' => [
        'clear' => [
            'message' => 'Limpar cache',
            // Localmente só limpa
            'cleared' => 'Cache limpa',
            'optimized' => 'Cache limpa e otimizada',
            'not_optimized' => 'Cache limpa, mas a otimização falhou: :error',
            'failed' => 'A limpeza da cache falhou: :error',
        ],
    ],

    'mail' => [
        'test' => [
            'message' => 'Email de teste',
            'prompt' => 'Enviar um email de teste para:',
            'success' => 'Email de teste enviado para :email, pelo mailer :mailer',
        ],
    ],

    'maintenance' => [
        'enable' => [
            'message' => 'Ativar manutenção',
            'confirm' => 'Ativar a manutenção? O site fica indisponível para todos, exceto este browser, que mantém o acesso durante 12 horas.',
            'success' => 'Manutenção ativada',
        ],
        'disable' => [
            'message' => 'Desativar manutenção',
            'success' => 'Manutenção desativada',
        ],
    ],

    'build' => [
        'message' => 'Compilar',
        'success' => 'Código compilado',
    ],
];
