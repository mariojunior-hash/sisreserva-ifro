<?php
/**
 * SisReserva IFRO — Rodapé Padrão (Footer)
 * Informações Institucionais e Inclusão do JavaScript Vanilla
 */

declare(strict_types=1);
?>
</main> <!-- Fecha .main-content -->

<footer class="footer">
  <div class="footer-container">
    <p>
      <span class="footer-strong"><?= e(APP_NAME) ?></span> &copy; <?= date('Y') ?> — 
      Instituto Federal de Educação, Ciência e Tecnologia de Rondônia (IFRO)
    </p>
    <p style="font-size: 0.775rem; margin-top: 0.25rem;">
      Campus Porto Velho Calama &bull; Componente Curricular: Desenvolvimento de Sistemas
    </p>
  </div>
</footer>

<script src="<?= ASSETS_URL ?>/js/app.js"></script>
</body>
</html>
