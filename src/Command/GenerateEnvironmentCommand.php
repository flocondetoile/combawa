<?php

namespace Drupal\Console\Combawa\Command;

use Drupal\Console\Command\Shared\ConfirmationTrait;
use Drupal\Console\Core\Command\Command;
use Drupal\Console\Core\Utils\StringConverter;
use Drupal\Console\Combawa\Generator\EnvironmentGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class GenerateEnvironmentCommand extends Command {

  use ConfirmationTrait;

  /**
   * @var EnvironmentGenerator
   */
  protected $generator;

  /**
   * @var string The document root absolute path.
   */
  protected $appRoot;

  /**
   * ProfileCommand constructor.
   *
   * @param EnvironmentGenerator $generator
   * @param StringConverter  $stringConverter
   * @param string           $app_root
   */
  public function __construct(
    EnvironmentGenerator $generator,
    $app_root
  ) {
    $this->generator = $generator;
    $this->appRoot = $app_root;
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure()
  {
    $this
      ->setName('combawa:generate-environment')
      ->setAliases(['cge'])
      ->setDescription('Generate the project .env file.')
      ->addOption(
        'environment',
        null,
        InputOption::VALUE_REQUIRED,
        'The build environment (prod, preprod or dev).'
      )
      ->addOption(
        'environment-url',
        null,
        InputOption::VALUE_REQUIRED,
        'The URL on which the project is reachable for this environment.'
      )
      ->addOption(
        'backup-db',
        null,
        InputOption::VALUE_NONE,
        'Backup the database on each build.'
      )
      ->addOption(
        'fetch-dump',
        null,
        InputOption::VALUE_NONE,
        'Fetch a database dump from a remote server before the build.'
      )
      ->addOption(
        'ssh-config-name',
        null,
        InputOption::VALUE_REQUIRED,
        'The remote server name where to find the dump.'
      )
      ->addOption(
        'ssh-dump-path',
        null,
        InputOption::VALUE_REQUIRED,
        'The remote server path where to find the dump.'
      )
      ->addOption(
        'dump-file-name',
        null,
        InputOption::VALUE_REQUIRED,
        'The name of the local dump file to load before building.'
      )
      ->addOption(
        'db-host',
        null,
        InputOption::VALUE_REQUIRED,
        'The host of the local database.'
      )
      ->addOption(
        'db-port',
        null,
        InputOption::VALUE_REQUIRED,
        'The port of the local database.'
      )
      ->addOption(
        'db-name',
        null,
        InputOption::VALUE_REQUIRED,
        'The name of the local database.'
      )
      ->addOption(
        'db-user',
        null,
        InputOption::VALUE_REQUIRED,
        'The user name of the local database.'
      )
      ->addOption(
        'db-password',
        null,
        InputOption::VALUE_OPTIONAL,
        'The password of the local database.'
      );
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    // @see use Drupal\Console\Command\Shared\ConfirmationTrait::confirmOperation
    if (!$this->confirmOperation()) {
      return 1;
    }

    $this->generator->generate([
      'app_root' => $this->appRoot,
      'environment' => $this->validateEnvironment($input->getOption('environment')),
      'environment_url' => $this->validateUrl($input->getOption('environment-url')),
      'backup_base' => (bool) $input->getOption('backup-db'),
      'fetch_dump' => (bool) $input->getOption('fetch-dump'),
      'ssh_config_name' => $input->getOption('ssh-config-name'),
      'ssh_dump_path' => $input->getOption('ssh-dump-path'),
      'dump_file_name' => $input->getOption('dump-file-name'),
      'db_host' => $input->getOption('db-host'),
      'db_port' => $input->getOption('db-port'),
      'db_name' => $input->getOption('db-name'),
      'db_user' => $input->getOption('db-user'),
      'db_password' => $input->getOption('db-password'),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    try {
      $environment = $input->getOption('environment') ? $this->validateEnvironment($input->getOption('environment')) : null;
    } catch (\Exception $error) {
      $this->getIo()->error($error->getMessage());

      return 1;
    }

    if (!$environment) {
      $environment = $this->getIo()->choice(
        'Which kind of environment is it?',
        ['dev', 'preprod', 'prod'],
        'prod'
      );
      $input->setOption('environment', $environment);
    }

    if ($environment != 'prod') {

      try {
        $environment_url = $input->getOption('environment-url') ? $this->validateUrl($input->getOption('environment-url')) : null;
      } catch (\Exception $error) {
        $this->getIo()->error($error->getMessage());

        return 1;
      }

      if (!$environment_url) {
        $environment_url = $this->getIo()->ask(
          'What is the URL of the project for the ' . $environment . ' environment?',
          'https://' . $environment . '.happyculture.coop',
          function ($environment_url) {
            return $this->validateUrl($environment_url);
          }
        );
        $input->setOption('environment-url', $environment_url);
      }

      try {
        $backup_db = $input->getOption('backup-db') ? (bool) $input->getOption('backup-db') : null;
      } catch (\Exception $error) {
        $this->getIo()->error($error->getMessage());

        return 1;
      }

      if (!$backup_db) {
        $backup_db = $this->getIo()->confirm(
          'Do you want the database to be backed up before each build?',
          TRUE
        );
        $input->setOption('backup-db', $backup_db);
      }

      try {
        $fetch_dump = $input->getOption('fetch-dump') ? (bool) $input->getOption('fetch-dump') : null;
      } catch (\Exception $error) {
        $this->getIo()->error($error->getMessage());

        return 1;
      }

      if (!$fetch_dump) {
        $fetch_dump = $this->getIo()->confirm(
          'Do you want the database dump to be fetched from a remote serveur before each build?',
          TRUE
        );
        $input->setOption('fetch-dump', $fetch_dump);
      }

      if ($fetch_dump) {

        try {
          $ssh_config_name = $input->getOption('ssh-config-name');
        } catch (\Exception $error) {
          $this->getIo()->error($error->getMessage());

          return 1;
        }

        if (!$ssh_config_name) {
          $ssh_config_name = $this->getIo()->ask(
            'What is the name for the dump remote server in your ~/.ssh/config file?',
            'my_remote'
          );
          $input->setOption('ssh-config-name', $ssh_config_name);
        }

        try {
          $ssh_dump_path = $input->getOption('ssh-dump-path');
        } catch (\Exception $error) {
          $this->getIo()->error($error->getMessage());

          return 1;
        }

        if (!$ssh_dump_path) {
          $ssh_dump_path = $this->getIo()->ask(
            'What is the full path of the dump file on the remote server?',
            '/home/dumps/my_dump.sql.gz'
          );
          $input->setOption('ssh-dump-path', $ssh_dump_path);
        }

      }

      try {
        $dump_file_name = $input->getOption('dump-file-name');
      } catch (\Exception $error) {
        $this->getIo()->error($error->getMessage());

        return 1;
      }

      if (!$dump_file_name) {
        $dump_file_name = $this->getIo()->ask(
          'What is the local name of the dump file to be loaded before the builds? Do not include the .gz extension.',
          'reference_dump.sql'
        );
        $input->setOption('dump-file-name', $dump_file_name);
      }
    }

    try {
      $db_host = $input->getOption('db-host');
    } catch (\Exception $error) {
      $this->getIo()->error($error->getMessage());

      return 1;
    }

    if (!$db_host) {
      $db_host = $this->getIo()->ask(
        'What is the hostname of your database server?',
        'localhost'
      );
      $input->setOption('db-host', $db_host);
    }

    try {
      $db_port = $input->getOption('db-port');
    } catch (\Exception $error) {
      $this->getIo()->error($error->getMessage());

      return 1;
    }

    if (!$db_port) {
      $db_port = $this->getIo()->ask(
        'What is the port of your database server?',
        '3306'
      );
      $input->setOption('db-port', $db_port);
    }

    try {
      $db_name = $input->getOption('db-name');
    } catch (\Exception $error) {
      $this->getIo()->error($error->getMessage());

      return 1;
    }

    if (!$db_name) {
      $db_name = $this->getIo()->ask(
        'What is the name of your database?',
        'drupal8'
      );
      $input->setOption('db-name', $db_name);
    }

    try {
      $db_user = $input->getOption('db-user');
    } catch (\Exception $error) {
      $this->getIo()->error($error->getMessage());

      return 1;
    }

    if (!$db_user) {
      $db_user = $this->getIo()->ask(
        'What is the user name of your database?',
        'root'
      );
      $input->setOption('db-user', $db_user);
    }

    try {
      $db_password = $input->getOption('db-password');
    } catch (\Exception $error) {
      $this->getIo()->error($error->getMessage());

      return 1;
    }

    if (!$db_password) {
      $db_password = $this->getIo()->askEmpty(
        'What is the password of your database?',
        ''
      );
      $input->setOption('db-password', $db_password);
    }
  }

  /**
   * Validates an environment name.
   *
   * @param string $env
   *   The environment name.
   * @return string
   *   The environment name.
   * @throws \InvalidArgumentException
   */
  protected function validateEnvironment($env) {
    $env = strtolower($env);
    if (in_array($env, ['dev', 'preprod', 'prod'])) {
      return $env;
    }
    else {
      throw new \InvalidArgumentException(sprintf('Environment name "%s" is invalid (only dev, prod or preprod allowed).', $env));
    }
  }

  /**
   * Validates an url.
   *
   * @param string $url
   *   The url to validate.
   *
   * @return string
   *   The url.
   */
  protected function validateUrl($url) {
    $parts = parse_url($url);
    if ($parts === FALSE) {
      throw new \InvalidArgumentException(
        sprintf(
          '"%s" is a malformed url.',
          $url
        )
      );
    }
    elseif (empty($parts['scheme']) || empty($parts['host'])) {
      throw new \InvalidArgumentException(
        sprintf(
          'Please specify a full URL with scheme and host instead of "%s".',
          $url
        )
      );
    }
    return $url;
  }

}
