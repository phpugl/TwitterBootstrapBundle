<?php
namespace Ruian\TwitterBootstrapBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Ruian\TwitterBootstrapBundle\Exception\TwitterBootstrapVersionException;
use lessc;

class CompilerCommand extends ContainerAwareCommand
{
    protected $path_twitter;
    protected $path_resources;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->path_resources = __DIR__ . '/../Resources/public/';
        $this->path_twitter = __DIR__ . '/../../../../../twitter/bootstrap/';
    }

    protected function configure()
    {
        $this
            ->setName('twitter-bootstrap:compile')
            ->setDescription('Compile a version of twitter-bootstrap and paste it into RuianTwitterBundle public folder')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (true === $this->writeCss($output)) {
            $output->writeln('<info>Success, bootstrap.css has been written in /Ruian/TwitterBootstrapBundle/Resources/public/css/bootstrap.css</info>');
        }

        if (true === $this->writeJs($output)) {
            $output->writeln('<info>Success, bootstrap.js has been written in /Ruian/TwitterBootstrapBundle/Resources/public/js/bootstrap.js</info>');
        }

        if (true === $this->copyImages($output)) {
            $output->writeln('<info>Success, bootstrap images have been copied to /Ruian/TwitterBootstrapBundle/Resources/public/img</info>');
        }
    }

    protected function writeCss($output)
    {
        $out = $this->path_resources . 'css/';
    
        if (!is_dir($out)) {
            mkdir($out, 0777, true);
        }
        
        lessc::ccompile($this->path_twitter . 'less/bootstrap.less', $out . 'bootstrap.css');

        $output->writeln('<comment>Writing bootstrap.css from bootstrap.less</comment>');
        $output->writeln('<comment>You can add bundles/ruiantwitterbootstrap/css/bootstrap.css to your layout</comment>');

        lessc::ccompile($this->path_twitter . 'less/responsive.less', $out . 'bootstrap-responsive.css');

        $output->writeln('<comment>Writing bootstrap-responsive.css from responsive.less</comment>');
        $output->writeln('<comment>You can add bundles/ruiantwitterbootstrap/css/bootstrap-responsive.css to your layout</comment>');

        return true;
    }

    protected function copyImages($output)
    {
        $out = $this->path_resources . 'img/';

        if (!is_dir($out)) {
            mkdir($out, 0777, true);
        }

        foreach (glob($this->path_twitter . '/img/*') as $image) {
            copy($image, $out . basename($image));
        }

        return true;
    }

    protected function writeJs($output)
    {
        $in = $this->path_twitter . 'js/';
        $out = $this->path_resources . 'js/';

        if (!is_dir($out)) {
            mkdir($out, 0777, true);
        }

        //here we use finder only to add some new files if bootstrap adds them
        //default bootstrap files, order is important
        $files = array(
          'bootstrap-transition.js',
          'bootstrap-alert.js',
          'bootstrap-modal.js',
          'bootstrap-dropdown.js',
          'bootstrap-scrollspy.js',
          'bootstrap-tab.js',
          'bootstrap-tooltip.js',
          'bootstrap-popover.js',          
          'bootstrap-button.js',
          'bootstrap-collapse.js',
          'bootstrap-carousel.js',
          'bootstrap-typeahead.js'
        );

        $finder = new Finder();
        $finder->depth('== 0');
        $finder->files()->in($in)->name('*.js');

        foreach ($finder as $file) {
          $baseFile = basename($file);
          if (!in_array($baseFile, $files)) {
            $output->writeln(sprintf('<comment>Found NEW file "%s", bootstrap has new javascripts?</comment>', $baseFile));
            $files[] = $baseFile;
          }
        }

        $bootstrapjs = null;

        foreach ($files as $file) {
            $bootstrapjs .= file_get_contents(realpath($in . $file));
            $output->writeln('<comment>Adding '.$file.'</comment>');
        }

        file_put_contents($out .'bootstrap.js', $bootstrapjs);

        $output->writeln('<comment>Writing bootstrap.js</comment>');
        $output->writeln('<comment>You can add bundles/ruiantwitterbootstrap/js/bootstrap.js to your layout</comment>');

        return true;
    }
}
