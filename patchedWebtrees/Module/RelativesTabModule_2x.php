<?php

namespace Cissee\WebtreesExt\Module;

use Cissee\WebtreesExt\ModuleView;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleTabInterface;
use Fisharebest\Webtrees\Module\ModuleTabTrait;
use Vesta\Model\GenericViewElement;

class RelativesTabModule_2x extends AbstractModule implements ModuleTabInterface {

  use ModuleTabTrait;

  protected $viewName = 'modules/relatives/tab';

  /** @var string The directory where the module is installed */
  protected $directory;

  public function __construct($directory) {
    $this->directory = $directory;
  }

  public function setViewName($viewName) {
    $this->viewName = $viewName;
  }

  /**
   * How should this module be labelled on tabs, menus, etc.?
   *
   * @return string
   */
  public function getTitle(): string {
    return /* I18N: Name of a module */ I18N::translate('Families');
  }

  /**
   * A sentence describing what this module does.
   *
   * @return string
   */
  public function getDescription(): string {
    return /* I18N: Description of the “Families” module */ I18N::translate('A tab showing the close relatives of an individual.');
  }

  /**
   * The user can re-arrange the tab order, but until they do, this
   * is the order in which tabs are shown.
   *
   * @return int
   */
  public function defaultTabOrder(): int {
    return 20;
  }

  /** {@inheritdoc} */
  public function getTabContent(Individual $individual): string {
    $tree = $individual->tree();
    if ($tree->getPreference('SHOW_PRIVATE_RELATIONSHIPS')) {
      $fam_access_level = Auth::PRIV_HIDE;
    } else {
      $fam_access_level = Auth::accessLevel($tree);
    }

    //[RC] additions
    $outputBeforeTab = $this->getOutputBeforeTab($individual);
    $outputAfterTab = $this->getOutputAfterTab($individual);
    $outputInDescriptionbox = $this->getOutputInDescriptionbox($individual);
    $outputAfterDescriptionbox = $this->getOutputAfterDescriptionbox($individual);

    $view = ModuleView::make($this->directory, $this->viewName, [
                'fam_access_level' => $fam_access_level,
                'can_edit' => $individual->canEdit(),
                'individual' => $individual,
                'parent_families' => $individual->childFamilies(),
                'spouse_families' => $individual->spouseFamilies(),
                'step_child_familiess' => $individual->spouseStepFamilies(),
                'step_parent_families' => $individual->childStepFamilies(),
                //[RC] additions
                'moduleName' => $this->name(),
                'outputFamilyAfterSubHeadersFunction' => function (Family $family, $type) {
                  return $this->getOutputFamilyAfterSubHeaders($family, $type);
                },
                'printFamilyChild' => function (Family $family, Individual $child) {
                  return $this->printFamilyChild($family, $child);
                },
                'outputBeforeTab' => $outputBeforeTab,
                'outputAfterTab' => $outputAfterTab,
                'outputInDescriptionbox' => $outputInDescriptionbox,
                'outputAfterDescriptionbox' => $outputAfterDescriptionbox
    ]);

    return $view;
  }

  /** {@inheritdoc} */
  public function hasTabContent(Individual $individual): bool {
    return true;
  }

  /** {@inheritdoc} */
  public function isGrayedOut(Individual $individual): bool {
    return false;
  }

  /** {@inheritdoc} */
  public function canLoadAjax(): bool {
    return false;
  }

  //[RC] override hooks

  protected function getOutputBeforeTab(Individual $person) {
    return new GenericViewElement('', '');
  }

  protected function getOutputAfterTab(Individual $person) {
    return new GenericViewElement('', '');
  }

  protected function getOutputInDescriptionBox(Individual $person) {
    return new GenericViewElement('', '');
  }

  protected function getOutputAfterDescriptionBox(Individual $person) {
    return new GenericViewElement('', '');
  }

  /**
   *
   * @param Family $family
   * @param string $type
   */
  protected function getOutputFamilyAfterSubHeaders(Family $family, $type) {
    return new GenericViewElement('', '');
  }

  protected function printFamilyChild(Family $family, Individual $child) {
    
  }

}
