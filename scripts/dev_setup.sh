#!/usr/bin/env bash

install_composer() {
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php -r "if (hash_file('sha384', 'composer-setup.php') === '93b54496392c062774670ac18b134c3b3a95e5a5e5c8f1a9f115f203b75bf9a129d5daa8ba6a13e2cc8a1da0806388a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
  php composer-setup.php
  php -r "unlink('composer-setup.php');"
}

add_to_path() {
  mkdir -p "$HOME/bin"
  mv composer.phar "$HOME/bin"
  mv $HOME/bin/composer{.phar,}
  chmod u+x "$HOME/bin/composer"

  if [[ -f "$HOME/.zshrc" ]]; then
    echo "PATH=\$PATH:$HOME/bin" >> "$HOME/.zshrc"
    echo "Please: source ~/.zshrc"
  elif [[ -f "$HOME/.bashrc" ]]; then
    echo "PATH=\$PATH:$HOME/bin" >> "$HOME/.bashrc"
    echo "Please: source ~/.bashrc"
  elif [[ -f "$HOME/.bash_profile" ]]; then
    echo "PATH=\$PATH:$HOME/bin" >> "$HOME/.bash_profile"
    echo "Please: source ~/.bash_profile"
  fi
}

main() {
  install_composer
  add_to_path

  composer install
}

main "$@"
