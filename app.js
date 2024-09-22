/**
 * App
 * Defined an App 
 */
var mainApp = mainApp || {} //Define App namespace.


/**
 * Define app information
 */
var appInfo={
  name:'UPSENHANCED',
  appPath:'/apps/ups-enhanced',
  version:'1.0.0',
  type:'INTERNET',
  menuName:'UPS ENHANCED'
}

/**
 * Constructor UNAS App
 */
mainApp.App = function () {
  this.id = appInfo.name
  this.name = appInfo.name
  this.version = appInfo.version
  this.active = false
  this.menuIcon = `${appInfo.appPath}/images/logo.png?v=${appInfo.version}&`
  this.shortcutIcon = `${appInfo.appPath}/images/logo.png?v=${appInfo.version}&`
  this.entryUrl = `${appInfo.appPath}/index.html?v=${appInfo.version}&`
  var self = this
  this.ServiceAppWindow = function () {
    if (UNAS.CheckAppState(appInfo.name)) {
      return false
    }
    self.window = new MUI.Window({
      id: `${appInfo.name}AppWindow`,
      title: UNAS._(`${appInfo.name}`),
      icon: `${appInfo.appPath}/images/logo_small.png?v=${appInfo.version}&`,
      loadMethod: 'xhr',
      width: 750,
      height: 480,
      maximizable: false,
      resizable: true,
      scrollbars: false,
      resizeLimit: { x: [200, 2000], y: [150, 1500] },
      contentURL: `${appInfo.appPath}/index.html?v=${appInfo.version}&`,
      require: { css: [`${appInfo.appPath}/css/index.css`] },
      onBeforeBuild: function () {
        UNAS.SetAppOpenedWindow(appInfo.name, `${appInfo.name}AppWindow`)
      },
    })
  }
  this.ServiceUninstall = function () {
    UNAS.RemoveDesktopShortcut(appInfo.name)
    UNAS.RemoveMenu(appInfo.name)
    UNAS.RemoveAppFromGroups(appInfo.name, 'ControlPanel')
    UNAS.RemoveAppFromApps(appInfo.name)
  }
  new UNAS.Menu(
    'UNAS_App_Internet_Menu',
    appInfo.menuName,
    this.menuIcon,
    appInfo.menuName,
    '',
    this.ServiceAppWindow
  )
  new UNAS.RegisterToAppGroup(
    appInfo.menuName,
    'ControlPanel',
    {
      Type: 'Internet',
      Location: 1,
      Icon: this.shortcutIcon,
      Url: this.entryUrl,
    },
    {}
  )
  var OnChangeLanguage = function (e) {
    UNAS.SetMenuTitle(appInfo.name, UNAS._(appInfo.name)) //translate menu
    if (typeof self.window !== 'undefined') {
      UNAS.SetWindowTitle(`${appInfo.name}AppWindow`, UNAS._(appInfo.name))
    }
  }
  UNAS.LoadTranslation(
    `${appInfo.appPath}/languages/Translation?v=${this.version}`,
    OnChangeLanguage
  )
  UNAS.Event.addEvent('ChangeLanguage', OnChangeLanguage)
  UNAS.CreateApp(
    this.name,
    this.shortcutIcon,
    this.ServiceAppWindow,
    this.Serviceninstall
  )
}

new mainApp.App()
