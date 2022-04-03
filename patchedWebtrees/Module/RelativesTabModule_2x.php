<?php

namespace Cissee\WebtreesExt\Module;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\RelativesTabModule;
use Vesta\Model\GenericViewElement;
use function view;

class RelativesTabModule_2x extends RelativesTabModule {

  protected $viewName = 'modules/relatives/tab';

  protected function getViewName(): string {
    return $this->viewName;
  }

  protected function getFamilyViewName(): string {
    return 'modules/relatives/family';
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

    $view = view($this->getViewName(), [
                'fam_access_level' => $fam_access_level,
                'can_edit' => $individual->canEdit(),
                'individual' => $individual,
                'parent_families' => $individual->childFamilies(),
                'spouse_families' => $individual->spouseFamilies(),
                'step_child_familiess' => $individual->spouseStepFamilies(),
                'step_parent_families' => $individual->childStepFamilies(),
                //[RC] additions
                'familyViewName' => $this->getFamilyViewName(),
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
