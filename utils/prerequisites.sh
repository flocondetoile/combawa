#!/usr/bin/env bash

echo -e ""
echo -e "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~"

# Check predeploy action script.
echo -e ""
echo -e "${BLUE}Verifying predeploy action script.${NC}"
if [ ! -f "$APP_SCRIPTS_DIR/predeploy_actions.sh" ]; then
  echo -e "${YELLOW}There is no predeploy actions script at the moment or its not readable.${NC}"
  echo -e "${ORANGE}You should run the following command to initialize it: 'drupal combawa:generate-project'.${NC}"
  echo ""
  exit -1
fi
echo -e "${GREEN}Predeploy actions script... OK!${NC}"

echo -e ""
echo -e "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~"

# Check postdeploy actions script.
echo -e ""
echo -e "${BLUE}Verifying postdeploy action script.${NC}"
if [ ! -f "$APP_SCRIPTS_DIR/postdeploy_actions.sh" ]; then
  echo -e "${YELLOW}There is no postdeploy actions script at the moment or its not readable.${NC}"
  echo -e "${ORANGE}You should run the following command to initialize it: 'drupal combawa:generate-project'.${NC}"
  echo ""
  exit -1
fi
echo -e "${GREEN}Postdeploy actions script... OK!${NC}"

echo -e ""
echo -e "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~"

# Preliminary verification to avoid running actions
# if the requiprements are not met.
case $COMBAWA_BUILD_MODE in
  "install" )
    echo -e ""
    echo -e "${BLUE}Verifying install.sh action script.${NC}"
    if [ ! -f "$APP_SCRIPTS_DIR/install.sh" ]; then
      echo -e "${YELLOW}There is no <app>/scripts/install.sh script at the moment or its not readable.${NC}"
      echo -e "${ORANGE}You should run the following command to initialize it: 'drupal combawa:generate-project'.${NC}"
      echo ""
      exit -1
    fi
    echo -e "${GREEN}Install.sh check... OK!${NC}"
    ;;
  "update" )
    echo -e ""
    echo -e "${BLUE}Verifying update.sh action script.${NC}"
    if [ ! -f "$APP_SCRIPTS_DIR/update.sh" ]; then
      echo -e "${YELLOW}There is no <app>/scripts/update.sh script at the moment or its not readable.${NC}"
      echo -e "${ORANGE}You should run the following command to initialize it: 'drupal combawa:generate-project'.${NC}"
      echo ""
      exit -1
    fi
    echo -e "${GREEN}Update.sh check... OK!${NC}"
    ;;
  "pull" )
    echo -e ""
    echo -e "${BLUE}Verifying pull.sh action script.${NC}"
    if [ ! -f "$APP_SCRIPTS_DIR/pull.sh" ]; then
      echo -e "${YELLOW}There is no <app>/scripts/pull.sh script at the moment or its not readable.${NC}"
      echo -e "${ORANGE}You should run the following command to initialize it: 'drupal combawa:generate-project'.${NC}"
      echo ""
      exit -1
    fi
    echo -e "${GREEN}Pull.sh check... OK!${NC}"
    ;;
  * )
    echo -e "${RED}Build mode unknown.${NC}"
    echo ""
    exit -1
    ;;
esac

echo -e ""
echo -e "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~"

# Test DB connection.
echo -e ""
echo -e "${BLUE}Verifying database connectivity.${NC}"
{
  $DRUSH sql-connect
} &> /dev/null || { # catch
  echo -e "${RED}The connection to the database is impossible.${NC}"
  echo ""
  exit -2
}

echo -e "${GREEN}DB connection... OK!${NC}"

echo -e ""
echo -e "~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~"
echo -e ""
