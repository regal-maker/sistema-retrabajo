<?php
// Supongamos que aquí haces tu consulta SQL: 
// $tickets = $db->query("SELECT * FROM tickets WHERE estado='abierto'")->fetchAll();

// Simulacro de datos para el ejemplo
$tickets = [
    [
        'id' => 4502, 
        'piezas' => '1x Bote / Mainframe, 2x Soporte Lateral, 1x Kit Tornillería', 
        'minutos_totales' => 135 // Ejemplo: 2 horas y 15 min
    ]
];

foreach ($tickets as $t) {
    // CAMBIO: Conversión de minutos a Horas y Minutos
    $horas = floor($t['minutos_totales'] / 60);
    $minutos = $t['minutos_totales'] % 60;
    
    $display_tiempo = ($horas > 0) ? "{$horas}h {$minutos}m" : "{$minutos}m";
    $clase_alerta = ($t['minutos_totales'] > 120) ? 'border-critico' : ''; // Rojo si pasa 2 horas
?>
    <div class="col-12 mb-3">
        <div class="card card-ticket p-0 <?php echo $clase_alerta; ?>">
            <div class="row g-0 align-items-center">
                <div class="col-md-2 tiempo-box">
                    <span class="tiempo-label">Transcurrido</span>
                    <span class="tiempo-valor"><?php echo $display_tiempo; ?></span>
                    <span class="badge bg-secondary mt-1">FOLIO #<?php echo $t['id']; ?></span>
                </div>

                <div class="col-md-7 p-3">
                    <div class="text-uppercase text-muted small fw-bold mb-2" style="letter-spacing: 0.5px;">Materiales en proceso</div>
                    <div class="pieza-container">
                        <?php 
                        $lista = explode(',', $t['piezas']);
                        foreach($lista as $p): ?>
                            <div class="pieza-badge">
                                <i class="bi bi-component"></i> <?php echo trim($p); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-md-3 text-center p-3">
                    <button onclick="finalizar(<?php echo $t['id']; ?>)" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm fw-bold">
                        FINALIZAR <i class="bi bi-check2-circle ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
