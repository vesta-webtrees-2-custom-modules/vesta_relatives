<?php

namespace Cissee\Webtrees\Hook\HookInterfaces;

use Closure;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Request;
use function app;


class RelativesTabExtenderUtils {
	
	public static function updateOrder(ModuleInterface $moduleForPrefsOrder, Request $request) {		
		$order = (array)$request->get('order');		
		//set als preference
		$pref = implode(',',$order);		
		$moduleForPrefsOrder->setPreference('ORDER', $pref);
  }
  
	public static function accessibleModules(ModuleInterface $moduleForPrefsOrder, Tree $tree, UserInterface $user): Collection {
    return self::sort($moduleForPrefsOrder, app()
						->make(ModuleService::class)
						->findByComponent(RelativesTabExtenderInterface::class, $tree, $user));
  }
  
	public static function modules(ModuleInterface $moduleForPrefsOrder, $include_disabled = false): Collection {
		return self::sort($moduleForPrefsOrder, app()
						->make(ModuleService::class)
						->findByInterface(RelativesTabExtenderInterface::class, $include_disabled));
	}
  
  private static function sort(ModuleInterface $moduleForPrefsOrder, Collection $coll): Collection {
    $pref = $moduleForPrefsOrder->getPreference('ORDER');
		if ($pref === null) {
			$pref = '';
		}
		$order = explode(',',$pref);
		$order = array_flip($order);
						
		return $coll
						->map(function (RelativesTabExtenderInterface $module) use ($order) {
							if (array_key_exists($module->name(), $order)) {
								$rank = $order[$module->name()];
								$module->setRelativesTabUIElementOrder($rank);
							}							
							return $module;
						})
						->sort(RelativesTabExtenderUtils::sorter());
  }
	
	public static function sorter(): Closure {
		return function (RelativesTabExtenderInterface $x, RelativesTabExtenderInterface $y): int {
			return $x->getRelativesTabUIElementOrder() <=> $y->getRelativesTabUIElementOrder();
		};
	}
}
