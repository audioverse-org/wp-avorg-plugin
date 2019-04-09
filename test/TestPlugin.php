<?php

final class TestPlugin extends Avorg\TestCase
{
	/** @var \Avorg\Plugin $plugin */
	protected $plugin;
	
	protected function setUp()
	{
		parent::setUp();
		
		$this->mockWordPress->setReturnValue("call", 5);
		$this->plugin = $this->factory->get("Plugin");
	}
	
	public function testInitInitsContentBits()
	{
		$contentBits = $this->factory->get("ContentBits");
		
		$this->plugin->init();

		$this->mockWordPress->assertMethodCalledWith(
			"add_shortcode",
			"avorg-bits",
			[$contentBits, "renderShortcode"]
		);
	}
	
	public function testInitInitsRouter()
	{
		$this->plugin->init();
		
		$this->mockWordPress->assertMethodCalled("add_rewrite_rule");
	}
	
	public function testEnqueueScripts()
	{
		$this->plugin->enqueueScripts();
		
		$this->mockWordPress->assertMethodCalled("wp_enqueue_style");
	}
	
	public function testEnqueueScriptsGetsStylesheetUrl()
	{
		$this->plugin->enqueueScripts();
		
		$this->mockWordPress->assertMethodCalled("plugins_url");
	}
	
	public function testEnqueueScriptsUsesPathWhenEnqueuingStyle()
	{
		$this->mockWordPress->setReturnValue("plugins_url", "path");
		
		$this->plugin->enqueueScripts();
		
		$this->mockWordPress->assertMethodCalledWith(
			"wp_enqueue_style",
			"avorgStyle",
			"path"
		);
	}
	
	public function testInitsListShortcode()
	{
		$listShortcode = $this->factory->get("ListShortcode");
		
		$this->plugin->init();
		
		$this->mockWordPress->assertMethodCalledWith(
			"add_shortcode",
			"avorg-list",
			[$listShortcode, "renderShortcode"]
		);
	}
	
	public function testEnqueuesVideoJsStyles()
	{
		$this->plugin->enqueueScripts();
		
		$this->mockWordPress->assertMethodCalledWith(
			"wp_enqueue_style",
			"avorgVideoJsStyle",
			"//vjs.zencdn.net/7.0/video-js.min.css"
		);
	}
	
	public function testSubscribesToAdminNoticeActionUsingAppropriateCallBackMethod()
	{
		$this->mockWordPress->assertActionAdded(
			"admin_notices",
			[$this->plugin, "renderAdminNotices"]
		);
	}
	
	public function testRenderAdminNoticesOutputsDefaultNotices()
	{
		$this->plugin->renderAdminNotices();
		
		$this->mockWordPress->assertMethodCalled("settings_errors");
	}
	
	public function testErrorNoticePostedWhenPermalinksTurnedOff()
	{
		$this->mockWordPress->setReturnValue("call", false);
		
		$this->plugin->renderAdminNotices();
		
		$this->mockTwig->assertErrorRenderedWithMessage("AVORG Warning: Permalinks turned off!");
	}
	
	public function testChecksPermalinkStructure()
	{
		$this->plugin->renderAdminNotices();
		
		$this->mockWordPress->assertMethodCalledWith("get_option", "permalink_structure");
	}
	
	public function testGetsAvorgApiUser()
	{
		$this->plugin->renderAdminNotices();
		
		$this->mockWordPress->assertMethodCalledWith("get_option", "avorgApiUser");
	}
	
	public function testGetsAvorgApiPass()
	{
		$this->plugin->renderAdminNotices();
		
		$this->mockWordPress->assertMethodCalledWith("get_option", "avorgApiPass");
	}
	
	public function testErrorNoticePostedWhenNoAvorgApiUser()
	{
		$this->mockWordPress->setReturnValue("call", false);
		
		$this->plugin->renderAdminNotices();
		
		$this->mockTwig->assertErrorRenderedWithMessage("AVORG Warning: Missing API username!");
	}
	
	public function testErrorNoticePostedWhenNoAvorgApiPass()
	{
		$this->mockWordPress->setReturnValue("call", false);
		
		$this->plugin->renderAdminNotices();
		
		$this->mockTwig->assertErrorRenderedWithMessage("AVORG Warning: Missing API password!");
	}

	/**
	 * @dataProvider pageNameProvider
	 * @param $pageName
	 * @throws ReflectionException
	 */
	public function testRegistersPageCallbacks($pageName)
	{
		$this->mockWordPress->assertPageRegistered($pageName);
	}

	public function pageNameProvider()
	{
		return [
			"Media Page" => ["Media"],
			"Topic Page" => ["Topic"],
			"Playlist Page" => ["Playlist"]
		];
	}

	public function testRegistersPwaCallbacks()
	{
		$pwa = $this->factory->get("Pwa");

		$this->mockWordPress->assertActionAdded(
			"wp_front_service_worker",
			[$pwa, "registerServiceWorker"]
		);
	}

	public function testRegistersLocalizationCallbacks()
	{
		$localization = $this->factory->get("Localization");

		$this->mockWordPress->assertActionAdded(
			"init",
			[$localization, "loadLanguages"]
		);
	}

	public function testRegistersActionCallbacks()
	{
		$action = $this->factory->get("AjaxAction\\Presentation");

		$this->mockWordPress->assertActionAdded(
			"wp_ajax_Avorg_AjaxAction_Presentation",
			[$action, "run"]
		);
	}

	/**
	 * @dataProvider scriptPathProvider
	 * @param $path
	 * @param bool $shouldRegister
	 * @param bool $isRelative
	 * @param null $pageClass
	 * @throws ReflectionException
	 */
	public function testRegistersScripts($path, $shouldRegister = true, $isRelative = false, $pageClass = null)
	{
		if ($pageClass) {
			/** @var Avorg\Page $page */
			$page = $this->factory->get($pageClass);

			$this->mockWordPress->setCurrentPageToPage(
				$page
			);

			$page->registerCallbacks();
		}

		$this->mockWordPress->runActions("wp", "wp_enqueue_scripts");

		$fullPath = $isRelative ? "AVORG_BASE_URL/$path" : $path;

		$args = [
			"wp_enqueue_script",
			"Avorg_Script_" . sha1($fullPath),
			$fullPath
		];

		if ($shouldRegister) {
			$this->mockWordPress->assertMethodCalledWith(...$args);
		} else {
			$this->mockWordPress->assertMethodNotCalledWith(...$args);
		}
	}

	public function scriptPathProvider()
	{
		return [
			"video js" => ["//vjs.zencdn.net/7.0/video.min.js"],
			"video js hls" => ["https://cdnjs.cloudflare.com/ajax/libs/videojs-contrib-hls/5.14.1/videojs-contrib-hls.min.js"],
			"don't init playlist.js on other pages" => ["script/playlist.js", false, true],
			"init playlist.js on playlist page" => ["script/playlist.js", true, true, "Page\\Playlist"],
			"polyfill.io" => ["https://polyfill.io/v3/polyfill.min.js?features=default"]
		];
	}
}