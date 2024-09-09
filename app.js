/**
 * DDNS GO App
 * Defined an App to manage ddns-go
 */
var DdnsGoApp = DdnsGoApp || {} //Define ddns-go App namespace.
/**
 * Constructor UNAS App
 */
DdnsGoApp.App = function () {
  this.id = 'DDNS GO'
  this.name = 'DDNS GO'
  this.version = '6.0.7'
  this.active = false
  this.menuIcon = '/apps/ddns-go/images/logo.png?v=6.0.7&'
  this.shortcutIcon = '/apps/ddns-go/images/logo.png?v=6.0.7&'
  this.entryUrl = '/apps/ddns-go/ddns-go.html?v=6.0.7&'
  var self = this
  this.DdnsGoAppWindow = function () {
    if (UNAS.CheckAppState('DDNS GO')) {
      return false
    }
    self.window = new MUI.Window({
      id: 'DdnsGoAppWindow',
      title: UNAS._('DDNS GO'),
      icon: '/apps/ddns-go/images/logo_small.png?v=6.0.7&',
      loadMethod: 'xhr',
      width: 750,
      height: 480,
      maximizable: false,
      resizable: true,
      scrollbars: false,
      resizeLimit: { x: [200, 2000], y: [150, 1500] },
      contentURL: '/apps/ddns-go/ddns-go.html?v=6.0.7&',
      require: { css: ['/apps/ddns-go/css/ddns-go.css'] },
      onBeforeBuild: function () {
        UNAS.SetAppOpenedWindow('DDNS GO', 'DdnsGoAppWindow')
      },
    })
  }
  this.DdnsGoUninstall = function () {
    UNAS.RemoveDesktopShortcut('DDNS GO')
    UNAS.RemoveMenu('DDNS GO')
    UNAS.RemoveAppFromGroups('DDNS GO', 'ControlPanel')
    UNAS.RemoveAppFromApps('DDNS GO')
  }
  new UNAS.Menu(
    'UNAS_App_Internet_Menu',
    this.name,
    this.menuIcon,
    'DDNS GO',
    '',
    this.DdnsGoAppWindow
  )
  new UNAS.RegisterToAppGroup(
    this.name,
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
    UNAS.SetMenuTitle('DDNS GO', UNAS._('DDNS GO')) //translate menu
    //UNAS.SetShortcutTitle('DDNS GO', UNAS._('DDNS GO'));
    if (typeof self.window !== 'undefined') {
      UNAS.SetWindowTitle('DdnsGoAppWindow', UNAS._('DDNS GO'))
    }
  }
  UNAS.LoadTranslation(
    '/apps/ddns-go/languages/Translation?v=' + this.version,
    OnChangeLanguage
  )
  UNAS.Event.addEvent('ChangeLanguage', OnChangeLanguage)
  UNAS.CreateApp(
    this.name,
    this.shortcutIcon,
    this.DdnsGoAppWindow,
    this.DdnsGoUninstall
  )
}

new DdnsGoApp.App()
