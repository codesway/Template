<?php
/**
 * Created by PhpStorm.
 * User: linliangliu
 * Date: 16/6/22
 * Time: 15:17
 */



class Template {

    private static $_instance = null;

    private $vars = [];

    private $staticize;
    private $tpl_path;
    private $com_path;
    private $com_prefix;
    private $com_ext;
    private $tpl_ext;
    private $pattern = '/\{\{(\S+)\}\}/i';
    private $replacement = '<?php echo \$this->$1 ?>';

    protected function __construct($conf){
        $this->staticize = !empty($conf['staticize']) ? true : false;  //是否静态化
        $this->tpl_path = $conf['tpl_path'];   //模板目录
        $this->tpl_ext = !empty($conf['tpl_ext']) ? $conf['tpl_ext'] : '.phtml';  //扩展名
        $this->com_path = $conf['com_path'];   //编译目录
        $this->com_prefix = !empty($conf['com_prefix']) ? $conf['com_prefix'] : 'com_';   //生成模板文件的前缀
        $this->com_ext = !empty($conf['com_ext']) ? $conf['com_ext'] : '.php';  //扩展名
    }

    public static function getInstance(array $conf) {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($conf);
        }

        return self::$_instance;
    }


    public function setVars(array $vars) {
        $this->vars = array_merge($vars, $this->vars);
    }

    public function setVar($name, $value) {
        $this->vars[$name] = $value;
    }

    public function display($fileName) {
        echo $this->render($fileName);
    }

    public function render($fileName) {
        $file = $this->tpl_path . rtrim($fileName, $this->tpl_ext) . $this->tpl_ext;
        $compileFile = $this->com_path . $this->com_prefix . sha1_file($file) . $this->com_ext;
        if (file_exists($compileFile) || (file_exists($compileFile) && filemtime($compileFile) >= filemtime($file))) {
            //如果编译内容没变, 或者被编译文件的时间大于模板文件, 那么就认为无改动
            ob_start();
            include $compileFile;
            $this->html = ob_get_clean();
            return $this->html;
        }

        $content = $this->_formatTags($this->_readFile($file));
        $this->_writeFile($compileFile, $content);
        ob_start();
        include $compileFile;
        $this->html = ob_get_clean();
        return $this->html;
    }

    private function _readFile($file) {

        if (!is_file($file)) {
            throw new Exception('Template: tpl_file not a file');
        }

        if (!file_exists($file)) {
            throw new Exception('Template: tpl_file not exist');
        }

        if (!is_readable($file)) {
            throw new Exception('Template: tpl_file not read');
        }

        return file_get_contents($file);
    }

    private function _writeFile($file, $content) {
        return file_put_contents($file, $content);
    }

    public function __get($name) {
        return $this->vars[$name];
    }

    //替换标签为原生php
    private function _formatTags($str) {
        return preg_replace($this->pattern, $this->replacement, $str);
    }

}