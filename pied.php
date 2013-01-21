</div><!-- fin div id=wrapper -->
<hr>
      <footer class="modal-footer">
				<div class="container">
					<p>&copy; Thelia 2012 
					- <a href="http://www.openstudio.fr/" target="_blank">&Eacute;dit&eacute; par OpenStudio</a>
					- <a href="http://forum.thelia.net/" target="_blank">Forum Thelia</a>
					- <a href="http://contrib.thelia.net/" target="_blank">Contributions Thelia</a>
                                        <span class="pull-right">interface par <a target="_blank" href="http://www.steaw-webdesign.com/">Steaw-Webdesign</a></span>
					</p>
                                        
				</div>
      </footer>
</div> <!-- fin div class=bg-image -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.js"></script>
    <script src="js/general.js"></script>
     <?php
 	ActionsAdminModules::instance()->inclure_module_admin("post");

	// Le parametre est passé par reference: utiliser un variable intermédiaire
	$tmp = "";
 	Tlog::ecrire($tmp);
 	echo $tmp;
    ?>