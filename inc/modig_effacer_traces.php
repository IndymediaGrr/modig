<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

// Permet de  supprimer les traces de modération après un certain temps
function cron_modig_effacer_traces($t) {
    include_spip('inc/cfg_config');
    $modo_delais = lire_config('modig/duree_conservation', 90);

    include_spip('base/abstract_sql');
    $query = "DELETE FROM `logs_mode` WHERE DATEDIFF(NOW(),`instant`)) > " . _q($modo_delais);
    spip_query($query);
    return 1;
}
?>
