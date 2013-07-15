<?php

class AdmParseur extends Parseur
{
    /**
     * 
     * @return \AdmParseur
     */
    public static function getInstance()
    {
        return new AdmParseur();
    }
    
    function __construct()
    {
        parent::__construct();
    }

    public function get_cache_dir()
    {
            return realpath($this->cache_dir);
    }

    function update_config()
    {
        foreach($_REQUEST as $var => $value)
        {
            if (! preg_match('/^'.Parseur::PREFIXE.'/', $var)) continue;

            Variable::ecrire($var, $value);
        }

        // Bug 1.4.3.1
        if (class_exists('CacheBase')) CacheBase::getCache()->reset_cache();
        
        $this->redirect();
    }

    function prepare_page()
    {
        $date = intval(Variable::lire(Parseur::PREFIXE.'_cache_check_time'));

        $this->last_date = $date > 0 ? date("d/m/Y H:i:s", $date) : 'Jamais';
        $this->next_date = date("d/m/Y H:i:s", $date + 3600 * intval(Variable::lire(Parseur::PREFIXE.'_cache_check_period')));

        if (is_dir($this->cache_dir)) $files = scandir ($this->cache_dir);

        $this->cache_count = count($files) - 2; // -2 pour '.' et '..'

    }

    public function make_yes_no_radio($var_name)
    {
        $val = Variable::lire($var_name);

        echo '<label class="radio inline">'.trad('Oui', 'admin').'<input type="radio" name="'.$var_name.'" value="1"'.($val == 1 ? ' checked="checked"':'').'></label>'. 
              '<label class="radio inline">' . trad('Non', 'admin').'<input type="radio" name="'.$var_name.'" value="0"'.($val == 0 ? ' checked="checked"':'').'>';
    }

    public function clear_cache()
    {
    	if ($dh = opendir($this->cache_dir))
    	{
            while ($file = readdir($dh))
            {
                if ($file == '.' || $file == '..') continue;

                unlink($this->cache_dir . '/' . $file);
            }
    	}
        
        ActionsModules::instance()->appel_module("clear_cache");
        $this->redirect();
    }

    public function check_cache()
    {
    	Analyse::cleanup_cache($this->cache_dir, 1);
        $this->redirect();
    }

    public function check_cache_dir()
    {
    	if (! is_dir($this->cache_dir))
    	{
            mkdir($this->cache_dir, 0777, true);

            @clearstatcache();
    	}
    }
    
    public function redirect()
    {
        redirige("cache.php");
    }
}