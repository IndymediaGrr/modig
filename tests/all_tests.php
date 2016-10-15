<?php
require_once('lanceur_spip.php');

include_spip('inc/modig');
class Test_moderation extends SpipTest {
    function test_modig_creer_auteur() {
      $this->assertTrue(modig_creer_auteur());
    }
}

?>
