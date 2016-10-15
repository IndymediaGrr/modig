<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

// Permet de  supprimer les articles après 1 mois sans modification.
function cron_modig_optimiser($t) {
    include_spip('base/optimiser');
    optimiser_base(3600 * 24 * 31);
    return 1;
}
?>
