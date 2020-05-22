<?php

namespace Cissee\Webtrees\Module\Relatives;

use Fisharebest\Webtrees\I18N;
use Vesta\ControlPanelUtils\Model\ControlPanelCheckbox;
use Vesta\ControlPanelUtils\Model\ControlPanelPreferences;
use Vesta\ControlPanelUtils\Model\ControlPanelSection;
use Vesta\ControlPanelUtils\Model\ControlPanelSubsection;

trait RelativesTabModuleTrait {

  protected function getMainTitle() {
    return I18N::translate('Vesta Families');
  }

  public function getShortDescription() {
    return
            I18N::translate('A tab showing the close relatives of an individual.') . ' ' .
            I18N::translate('Replacement for the original \'Families\' module.');
  }

  protected function getFullDescription() {
    $description = array();
    $description[] = /* I18N: Module Configuration */I18N::translate('An extended \'Families\' tab, with hooks for other custom modules.');
    $description[] = /* I18N: Module Configuration */I18N::translate('Intended as a replacement for the original \'Families\' module.');
    $description[] = /* I18N: Module Configuration */I18N::translate('Requires the \'%1$s Vesta Common\' module.', $this->getVestaSymbol());
    return $description;
  }

  protected function createPrefs() {
    $generalSub = array();
    $generalSub[] = new ControlPanelSubsection(
            /* I18N: Module Configuration */I18N::translate('Displayed title'),
            array(/*new ControlPanelCheckbox(                    
                I18N::translate('Include the %1$s symbol in the module title', $this->getVestaSymbol()),
                null,
                'VESTA',
                '1'),*/
        new ControlPanelCheckbox(
                /* I18N: Module Configuration */I18N::translate('Include the %1$s symbol in the tab title', $this->getVestaSymbol()),
                /* I18N: Module Configuration */I18N::translate('Deselect in order to have the tab appear exactly as the original tab.'),
                'VESTA_TAB',
                '1')));

    $sections = array();
    $sections[] = new ControlPanelSection(
            /* I18N: Module Configuration */I18N::translate('General'),
            null,
            $generalSub);

    return new ControlPanelPreferences($sections);
  }

}
