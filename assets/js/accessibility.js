/* ===========================================

  ACCESSIBILITY WIDGET — Evoke One Edition

  Oryginał: github.com/sinanisler/accessibility-widgets
  Konfiguracja: przekazywana przez wp_localize_script jako ACCESSIBILITY_WIDGET_CONFIG

=========================================== */

// ===========================================
// TRANSLATIONS
// ===========================================

const TRANSLATIONS = {
  en: { accessibilityMenu:'Accessibility Menu',closeAccessibilityMenu:'Close Accessibility Menu',accessibilityTools:'Accessibility Tools',resetAllSettings:'Reset All Settings',screenReader:'Screen Reader',voiceCommand:'Voice Command',textSpacing:'Text Spacing',pauseAnimations:'Pause Animations',hideImages:'Hide Images',dyslexiaFriendly:'Dyslexia Friendly',biggerCursor:'Bigger Cursor',lineHeight:'Line Height',fontSelection:'Font Selection',colorFilter:'Color Filter',textAlign:'Text Align',textSize:'Text Size',highContrast:'High Contrast',defaultFont:'Default Font',noFilter:'No Filter',default:'Default',screenReaderOn:'Screen reader on',screenReaderOff:'Screen reader off',voiceControlActivated:'Voice control activated',notSupportedBrowser:'is not supported in this browser',close:'Close',reset:'Reset',saturation:'Saturation',selectLanguage:'Select Language' },
  de: { accessibilityMenu:'Barrierefreiheitsmenü',closeAccessibilityMenu:'Barrierefreiheitsmenü schließen',accessibilityTools:'Barrierefreiheitswerkzeuge',resetAllSettings:'Alle Einstellungen zurücksetzen',screenReader:'Screenreader',voiceCommand:'Sprachbefehl',textSpacing:'Textabstand',pauseAnimations:'Animationen pausieren',hideImages:'Bilder ausblenden',dyslexiaFriendly:'Legasthenie-freundlich',biggerCursor:'Größerer Cursor',lineHeight:'Zeilenhöhe',fontSelection:'Schriftauswahl',colorFilter:'Farbfilter',textAlign:'Textausrichtung',textSize:'Textgröße',highContrast:'Hoher Kontrast',defaultFont:'Standardschrift',noFilter:'Kein Filter',default:'Standard',screenReaderOn:'Screenreader ein',screenReaderOff:'Screenreader aus',voiceControlActivated:'Sprachsteuerung aktiviert',notSupportedBrowser:'wird in diesem Browser nicht unterstützt',close:'Schließen',reset:'Zurücksetzen',saturation:'Sättigung',selectLanguage:'Sprache wählen' },
  es: { accessibilityMenu:'Menú de Accesibilidad',closeAccessibilityMenu:'Cerrar Menú',accessibilityTools:'Herramientas',resetAllSettings:'Restablecer',screenReader:'Lector de Pantalla',voiceCommand:'Comando de Voz',textSpacing:'Espaciado',pauseAnimations:'Pausar Animaciones',hideImages:'Ocultar Imágenes',dyslexiaFriendly:'Dislexia',biggerCursor:'Cursor Grande',lineHeight:'Altura de Línea',fontSelection:'Fuente',colorFilter:'Filtro de Color',textAlign:'Alineación',textSize:'Tamaño',highContrast:'Alto Contraste',defaultFont:'Predeterminada',noFilter:'Sin Filtro',default:'Predeterminado',screenReaderOn:'Lector activado',screenReaderOff:'Lector desactivado',voiceControlActivated:'Control de voz activado',notSupportedBrowser:'no compatible',close:'Cerrar',reset:'Restablecer',saturation:'Saturación',selectLanguage:'Idioma' },
  fr: { accessibilityMenu:'Menu Accessibilité',closeAccessibilityMenu:'Fermer',accessibilityTools:'Outils',resetAllSettings:'Réinitialiser',screenReader:"Lecteur d'Écran",voiceCommand:'Commande Vocale',textSpacing:'Espacement',pauseAnimations:'Pause Animations',hideImages:'Masquer Images',dyslexiaFriendly:'Dyslexie',biggerCursor:'Grand Curseur',lineHeight:'Hauteur de Ligne',fontSelection:'Police',colorFilter:'Filtre Couleur',textAlign:'Alignement',textSize:'Taille',highContrast:'Contraste Élevé',defaultFont:'Par Défaut',noFilter:'Sans Filtre',default:'Par Défaut',screenReaderOn:"Lecteur activé",screenReaderOff:"Lecteur désactivé",voiceControlActivated:'Contrôle vocal activé',notSupportedBrowser:'non supporté',close:'Fermer',reset:'Réinitialiser',saturation:'Saturation',selectLanguage:'Langue' },
  pl: { accessibilityMenu:'Menu Dostępności',closeAccessibilityMenu:'Zamknij Menu',accessibilityTools:'Narzędzia Dostępności',resetAllSettings:'Resetuj Ustawienia',screenReader:'Czytnik Ekranu',voiceCommand:'Komendy Głosowe',textSpacing:'Odstępy w Tekście',pauseAnimations:'Zatrzymaj Animacje',hideImages:'Ukryj Obrazki',dyslexiaFriendly:'Dla Dyslektyków',biggerCursor:'Większy Kursor',lineHeight:'Wysokość Linii',fontSelection:'Wybór Czcionki',colorFilter:'Filtr Kolorów',textAlign:'Wyrównanie Tekstu',textSize:'Rozmiar Tekstu',highContrast:'Wysoki Kontrast',defaultFont:'Domyślna Czcionka',noFilter:'Bez Filtra',default:'Domyślne',screenReaderOn:'Czytnik ekranu włączony',screenReaderOff:'Czytnik ekranu wyłączony',voiceControlActivated:'Sterowanie głosem aktywne',notSupportedBrowser:'nie jest obsługiwane w tej przeglądarce',close:'Zamknij',reset:'Resetuj',saturation:'Nasycenie',selectLanguage:'Wybierz Język' },
  it: { accessibilityMenu:'Menu Accessibilità',closeAccessibilityMenu:'Chiudi',accessibilityTools:'Strumenti',resetAllSettings:'Ripristina',screenReader:'Lettore Schermo',voiceCommand:'Comando Vocale',textSpacing:'Spaziatura',pauseAnimations:'Pausa Animazioni',hideImages:'Nascondi Immagini',dyslexiaFriendly:'Dislessia',biggerCursor:'Cursore Grande',lineHeight:'Altezza Linea',fontSelection:'Font',colorFilter:'Filtro Colore',textAlign:'Allineamento',textSize:'Dimensione',highContrast:'Alto Contrasto',defaultFont:'Predefinito',noFilter:'Nessun Filtro',default:'Predefinito',screenReaderOn:'Lettore attivo',screenReaderOff:'Lettore disattivo',voiceControlActivated:'Controllo vocale attivato',notSupportedBrowser:'non supportato',close:'Chiudi',reset:'Ripristina',saturation:'Saturazione',selectLanguage:'Lingua' },
  ru: { accessibilityMenu:'Меню Доступности',closeAccessibilityMenu:'Закрыть',accessibilityTools:'Инструменты',resetAllSettings:'Сбросить',screenReader:'Чтение с Экрана',voiceCommand:'Голос',textSpacing:'Интервал',pauseAnimations:'Пауза Анимации',hideImages:'Скрыть Изображения',dyslexiaFriendly:'Дислексия',biggerCursor:'Курсор',lineHeight:'Высота Строки',fontSelection:'Шрифт',colorFilter:'Фильтр',textAlign:'Выравнивание',textSize:'Размер',highContrast:'Контраст',defaultFont:'Стандарт',noFilter:'Без Фильтра',default:'Стандарт',screenReaderOn:'Чтение включено',screenReaderOff:'Чтение выключено',voiceControlActivated:'Голос активен',notSupportedBrowser:'не поддерживается',close:'Закрыть',reset:'Сбросить',saturation:'Насыщенность',selectLanguage:'Язык' },
  ar: { accessibilityMenu:'قائمة إمكانية الوصول',closeAccessibilityMenu:'إغلاق',accessibilityTools:'أدوات',resetAllSettings:'إعادة تعيين',screenReader:'قارئ الشاشة',voiceCommand:'صوتي',textSpacing:'تباعد',pauseAnimations:'إيقاف الحركة',hideImages:'إخفاء الصور',dyslexiaFriendly:'عسر القراءة',biggerCursor:'مؤشر أكبر',lineHeight:'ارتفاع الخط',fontSelection:'خط',colorFilter:'مرشح',textAlign:'محاذاة',textSize:'حجم',highContrast:'تباين',defaultFont:'افتراضي',noFilter:'بدون مرشح',default:'افتراضي',screenReaderOn:'قارئ مفعّل',screenReaderOff:'قارئ معطل',voiceControlActivated:'صوتي مفعّل',notSupportedBrowser:'غير مدعوم',close:'إغلاق',reset:'إعادة',saturation:'تشبع',selectLanguage:'اللغة' },
  'zh-cn': { accessibilityMenu:'辅助菜单',closeAccessibilityMenu:'关闭',accessibilityTools:'工具',resetAllSettings:'重置',screenReader:'屏幕阅读',voiceCommand:'语音',textSpacing:'间距',pauseAnimations:'暂停动画',hideImages:'隐藏图片',dyslexiaFriendly:'阅读障碍',biggerCursor:'大光标',lineHeight:'行高',fontSelection:'字体',colorFilter:'滤镜',textAlign:'对齐',textSize:'字号',highContrast:'高对比度',defaultFont:'默认',noFilter:'无滤镜',default:'默认',screenReaderOn:'阅读器开启',screenReaderOff:'阅读器关闭',voiceControlActivated:'语音已激活',notSupportedBrowser:'不支持',close:'关闭',reset:'重置',saturation:'饱和度',selectLanguage:'语言' },
  jp: { accessibilityMenu:'アクセシビリティ',closeAccessibilityMenu:'閉じる',accessibilityTools:'ツール',resetAllSettings:'リセット',screenReader:'スクリーンリーダー',voiceCommand:'音声',textSpacing:'文字間隔',pauseAnimations:'アニメーション停止',hideImages:'画像非表示',dyslexiaFriendly:'ディスレクシア',biggerCursor:'大きいカーソル',lineHeight:'行の高さ',fontSelection:'フォント',colorFilter:'フィルター',textAlign:'配置',textSize:'文字サイズ',highContrast:'高コントラスト',defaultFont:'デフォルト',noFilter:'フィルターなし',default:'デフォルト',screenReaderOn:'リーダーオン',screenReaderOff:'リーダーオフ',voiceControlActivated:'音声有効',notSupportedBrowser:'非対応',close:'閉じる',reset:'リセット',saturation:'彩度',selectLanguage:'言語' },
  pt: { accessibilityMenu:'Menu de Acessibilidade',closeAccessibilityMenu:'Fechar',accessibilityTools:'Ferramentas',resetAllSettings:'Redefinir',screenReader:'Leitor de Tela',voiceCommand:'Voz',textSpacing:'Espaçamento',pauseAnimations:'Pausar Animações',hideImages:'Ocultar Imagens',dyslexiaFriendly:'Dislexia',biggerCursor:'Cursor Grande',lineHeight:'Altura da Linha',fontSelection:'Fonte',colorFilter:'Filtro',textAlign:'Alinhamento',textSize:'Tamanho',highContrast:'Alto Contraste',defaultFont:'Padrão',noFilter:'Sem Filtro',default:'Padrão',screenReaderOn:'Leitor ativo',screenReaderOff:'Leitor desativado',voiceControlActivated:'Voz ativada',notSupportedBrowser:'não suportado',close:'Fechar',reset:'Redefinir',saturation:'Saturação',selectLanguage:'Idioma' },
  ko: { accessibilityMenu:'접근성 메뉴',closeAccessibilityMenu:'닫기',accessibilityTools:'도구',resetAllSettings:'초기화',screenReader:'스크린 리더',voiceCommand:'음성',textSpacing:'간격',pauseAnimations:'애니메이션 중지',hideImages:'이미지 숨기기',dyslexiaFriendly:'난독증',biggerCursor:'큰 커서',lineHeight:'줄 높이',fontSelection:'글꼴',colorFilter:'필터',textAlign:'정렬',textSize:'크기',highContrast:'고대비',defaultFont:'기본',noFilter:'필터 없음',default:'기본',screenReaderOn:'리더 켜짐',screenReaderOff:'리더 꺼짐',voiceControlActivated:'음성 활성화',notSupportedBrowser:'지원 안됨',close:'닫기',reset:'초기화',saturation:'채도',selectLanguage:'언어' },
  tr: { accessibilityMenu:'Erişilebilirlik',closeAccessibilityMenu:'Kapat',accessibilityTools:'Araçlar',resetAllSettings:'Sıfırla',screenReader:'Ekran Okuyucu',voiceCommand:'Ses',textSpacing:'Aralık',pauseAnimations:'Animasyonu Durdur',hideImages:'Resimleri Gizle',dyslexiaFriendly:'Disleksi',biggerCursor:'Büyük İmleç',lineHeight:'Satır Yüksekliği',fontSelection:'Yazı Tipi',colorFilter:'Filtre',textAlign:'Hizalama',textSize:'Boyut',highContrast:'Kontrast',defaultFont:'Varsayılan',noFilter:'Filtre Yok',default:'Varsayılan',screenReaderOn:'Okuyucu açık',screenReaderOff:'Okuyucu kapalı',voiceControlActivated:'Ses aktif',notSupportedBrowser:'desteklenmiyor',close:'Kapat',reset:'Sıfırla',saturation:'Doygunluk',selectLanguage:'Dil' },
  nl: { accessibilityMenu:'Toegankelijkheidsmenu',closeAccessibilityMenu:'Sluiten',accessibilityTools:'Hulpmiddelen',resetAllSettings:'Resetten',screenReader:'Schermlezer',voiceCommand:'Spraak',textSpacing:'Tekstafstand',pauseAnimations:'Animaties Pauzeren',hideImages:'Afbeeldingen Verbergen',dyslexiaFriendly:'Dyslexie',biggerCursor:'Grote Cursor',lineHeight:'Regelhoogte',fontSelection:'Lettertype',colorFilter:'Filter',textAlign:'Uitlijning',textSize:'Tekstgrootte',highContrast:'Hoog Contrast',defaultFont:'Standaard',noFilter:'Geen Filter',default:'Standaard',screenReaderOn:'Lezer aan',screenReaderOff:'Lezer uit',voiceControlActivated:'Spraak actief',notSupportedBrowser:'niet ondersteund',close:'Sluiten',reset:'Resetten',saturation:'Verzadiging',selectLanguage:'Taal' },
  hi: { accessibilityMenu:'पहुँच मेनू',closeAccessibilityMenu:'बंद करें',accessibilityTools:'उपकरण',resetAllSettings:'रीसेट करें',screenReader:'स्क्रीन रीडर',voiceCommand:'वॉयस',textSpacing:'स्पेसिंग',pauseAnimations:'एनिमेशन रोकें',hideImages:'चित्र छिपाएँ',dyslexiaFriendly:'डिस्लेक्सिया',biggerCursor:'बड़ा कर्सर',lineHeight:'लाइन ऊँचाई',fontSelection:'फ़ॉन्ट',colorFilter:'फ़िल्टर',textAlign:'संरेखण',textSize:'आकार',highContrast:'कंट्रास्ट',defaultFont:'डिफ़ॉल्ट',noFilter:'कोई फ़िल्टर नहीं',default:'डिफ़ॉल्ट',screenReaderOn:'रीडर चालू',screenReaderOff:'रीडर बंद',voiceControlActivated:'वॉयस सक्रिय',notSupportedBrowser:'समर्थित नहीं',close:'बंद',reset:'रीसेट',saturation:'संतृप्ति',selectLanguage:'भाषा' },
  sv: { accessibilityMenu:'Tillgänglighetsmeny',closeAccessibilityMenu:'Stäng',accessibilityTools:'Verktyg',resetAllSettings:'Återställ',screenReader:'Skärmläsare',voiceCommand:'Röst',textSpacing:'Textavstånd',pauseAnimations:'Pausa Animationer',hideImages:'Dölj Bilder',dyslexiaFriendly:'Dyslexi',biggerCursor:'Stor Markör',lineHeight:'Radhöjd',fontSelection:'Typsnitt',colorFilter:'Filter',textAlign:'Justering',textSize:'Textstorlek',highContrast:'Hög Kontrast',defaultFont:'Standard',noFilter:'Inget Filter',default:'Standard',screenReaderOn:'Läsare på',screenReaderOff:'Läsare av',voiceControlActivated:'Röst aktiv',notSupportedBrowser:'stöds ej',close:'Stäng',reset:'Återställ',saturation:'Mättnad',selectLanguage:'Språk' },
};

// Language detection and management
let currentLanguage = 'en';

function detectBrowserLanguage() {
  const browserLang = (navigator.language || navigator.userLanguage || 'en').toLowerCase();
  if (TRANSLATIONS[browserLang]) return browserLang;
  const langCode = browserLang.split('-')[0];
  if (TRANSLATIONS[langCode]) return langCode;
  if (browserLang.includes('zh')) return 'zh-cn';
  return 'en';
}

function setLanguage(lang) {
  if (TRANSLATIONS[lang]) {
    currentLanguage = lang;
    localStorage.setItem('accessibilityWidgetLanguage', lang);
    return true;
  }
  return false;
}

function getTranslation(key) {
  return (TRANSLATIONS[currentLanguage] && TRANSLATIONS[currentLanguage][key])
    || (TRANSLATIONS['en'] && TRANSLATIONS['en'][key])
    || key;
}

const savedLanguage = localStorage.getItem('accessibilityWidgetLanguage');
if (savedLanguage && TRANSLATIONS[savedLanguage]) {
  currentLanguage = savedLanguage;
} else {
  currentLanguage = detectBrowserLanguage();
  localStorage.setItem('accessibilityWidgetLanguage', currentLanguage);
}

// ===========================================
// CONFIGURATION — merged from wp_localize_script
// ===========================================

const DEFAULT_WIDGET_CONFIG = {
  enableHighContrast: true,
  enableBiggerText: true,
  enableTextSpacing: true,
  enablePauseAnimations: true,
  enableHideImages: true,
  enableDyslexiaFont: true,
  enableBiggerCursor: true,
  enableLineHeight: true,
  enableTextAlign: true,
  enableScreenReader: true,
  enableVoiceControl: true,
  enableFontSelection: true,
  enableColorFilter: true,
  enableSaturation: true,
  widgetWidth: '450px',
  widgetPosition: { side: 'right', right: '20px', left: '20px', bottom: '20px' },
  colors: { primary: '#1663d7', secondary: '#ffffff', optionBg: '#ffffff', optionText: '#333333', optionIcon: '#000000' },
  button: { size: '50px', borderRadius: '100px', iconSize: '40px', shadow: '0 4px 8px rgba(0,0,0,0.2)' },
  menu: { headerHeight: '50px', padding: '0 5px 5px 5px', optionPadding: '5px 5px', optionMargin: '5px', borderRadius: '8px', fontSize: '16px', titleFontSize: '16px', closeButtonSize: '34px' },
  typography: { fontFamily: 'Arial, sans-serif', fontSize: '16px', titleFontSize: '20px', titleFontWeight: '700', lineHeight: '1' },
  animation: { transition: '0.4s', hoverScale: '1.05' },
  gridLayout: { columns: '1fr 1fr', gap: '5px' },
};

function mergeConfigs(defaultConfig, userConfig) {
  const result = { ...defaultConfig };
  if (!userConfig) return result;
  for (const key in userConfig) {
    if (Object.prototype.hasOwnProperty.call(userConfig, key)) {
      if (typeof userConfig[key] === 'object' && userConfig[key] !== null && !Array.isArray(userConfig[key])) {
        result[key] = mergeConfigs(defaultConfig[key] || {}, userConfig[key]);
      } else {
        result[key] = userConfig[key];
      }
    }
  }
  return result;
}

const WIDGET_CONFIG = mergeConfigs(DEFAULT_WIDGET_CONFIG, window.ACCESSIBILITY_WIDGET_CONFIG || {});

// ===========================================
// VOICE COMMANDS
// ===========================================

const VOICE_COMMANDS = {
  en: { showMenu:['show menu','open menu','accessibility menu'],highContrast:['high contrast','contrast'],biggerText:['bigger text','text size','larger text'],textSpacing:['text spacing','spacing'],pauseAnimations:['pause animations','stop animations'],hideImages:['hide images'],dyslexiaFont:['dyslexia'],biggerCursor:['bigger cursor','cursor'],lineHeight:['line height'],textAlign:['text align'],saturation:['saturation'],fontSelection:['font'],colorFilter:['color filter'],screenReader:['screen reader'],voiceControl:['voice command','voice control'],resetAll:['reset all','reset'] },
  pl: { showMenu:['pokaż menu','otwórz menu'],highContrast:['wysoki kontrast'],biggerText:['większy tekst','rozmiar tekstu'],textSpacing:['odstępy tekstu'],pauseAnimations:['zatrzymaj animacje'],hideImages:['ukryj obrazki'],dyslexiaFont:['dysleksia'],biggerCursor:['większy kursor'],lineHeight:['wysokość linii'],textAlign:['wyrównanie'],saturation:['nasycenie'],fontSelection:['czcionka'],colorFilter:['filtr kolorów'],screenReader:['czytnik ekranu'],voiceControl:['sterowanie głosem'],resetAll:['resetuj wszystko','resetuj'] },
  de: { showMenu:['menü anzeigen','menü öffnen'],highContrast:['hoher kontrast'],biggerText:['größerer text'],textSpacing:['textabstand'],pauseAnimations:['animationen pausieren'],hideImages:['bilder ausblenden'],dyslexiaFont:['legasthenie'],biggerCursor:['größerer cursor'],lineHeight:['zeilenhöhe'],textAlign:['textausrichtung'],saturation:['sättigung'],fontSelection:['schrift'],colorFilter:['farbfilter'],screenReader:['screenreader'],voiceControl:['sprachsteuerung'],resetAll:['alles zurücksetzen'] },
};

// ===========================================
// STYLES
// ===========================================

const widgetStyles = `
  :host { all: initial; font-family: ${WIDGET_CONFIG.typography.fontFamily}; }
  * { box-sizing: border-box; }

  #snn-accessibility-fixed-button {
    position: fixed !important;
    ${WIDGET_CONFIG.widgetPosition.side}: ${WIDGET_CONFIG.widgetPosition[WIDGET_CONFIG.widgetPosition.side]} !important;
    bottom: ${WIDGET_CONFIG.widgetPosition.bottom} !important;
    z-index: 9999;
    background: ${WIDGET_CONFIG.colors.primary};
    padding: 5px;
    border-radius: 100%;
  }

  #snn-accessibility-button {
    background: ${WIDGET_CONFIG.colors.primary};
    border: none;
    border-radius: ${WIDGET_CONFIG.button.borderRadius};
    cursor: pointer;
    width: ${WIDGET_CONFIG.button.size};
    height: ${WIDGET_CONFIG.button.size};
    box-shadow: ${WIDGET_CONFIG.button.shadow};
    transition: ${WIDGET_CONFIG.animation.transition} !important;
    display: flex;
    justify-content: center;
    align-items: center;
    border: solid 2px white;
  }
  #snn-accessibility-button:hover { transform: scale(${WIDGET_CONFIG.animation.hoverScale}); }
  #snn-accessibility-button:focus { outline: 2px solid ${WIDGET_CONFIG.colors.secondary}; outline-offset: 2px; }
  #snn-accessibility-button svg { width: ${WIDGET_CONFIG.button.iconSize}; height: ${WIDGET_CONFIG.button.iconSize}; fill: ${WIDGET_CONFIG.colors.secondary}; pointer-events: none; }

  #snn-accessibility-menu {
    position: fixed;
    top: 0;
    ${WIDGET_CONFIG.widgetPosition.side}: 0;
    max-width: ${WIDGET_CONFIG.widgetWidth};
    width: 100%;
    height: 100vh;
    overflow-y: auto;
    background-color: #e2e2e2;
    padding: 0;
    display: none;
    font-family: ${WIDGET_CONFIG.typography.fontFamily};
    z-index: 999999;
    scrollbar-width: thin;
    line-height: 1 !important;
  }

  .snn-accessibility-option {
    font-size: ${WIDGET_CONFIG.menu.fontSize};
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-around;
    padding: 5px;
    width: 100%;
    background-color: ${WIDGET_CONFIG.colors.optionBg};
    color: ${WIDGET_CONFIG.colors.optionText};
    border: 3px solid ${WIDGET_CONFIG.colors.optionBg};
    cursor: pointer;
    border-radius: ${WIDGET_CONFIG.menu.borderRadius};
    transition: background-color ${WIDGET_CONFIG.animation.transition}, border-color ${WIDGET_CONFIG.animation.transition};
    line-height: ${WIDGET_CONFIG.typography.lineHeight} !important;
    gap: 5px;
    min-height: 105px;
  }
  .snn-accessibility-option:hover { border-color: ${WIDGET_CONFIG.colors.primary}; }
  .snn-accessibility-option.active { border-color: ${WIDGET_CONFIG.colors.primary}; }
  .snn-accessibility-option:disabled { opacity: 0.5; cursor: not-allowed; }

  .snn-icon { width: ${WIDGET_CONFIG.button.iconSize}; height: ${WIDGET_CONFIG.button.iconSize}; fill: ${WIDGET_CONFIG.colors.optionIcon}; flex-shrink: 0; }
  .snn-button-text { text-align: center; line-height: 1.2; font-size: 16px; font-weight: 600; }

  .snn-option-steps { display: flex; gap: 5px; align-items: center; justify-content: center; margin-top: 5px; }
  .snn-option-step { width: 30px; height: 6px; border-radius: 3px; background-color: #d0d0d0; transition: background-color ${WIDGET_CONFIG.animation.transition}; }
  .snn-option-step.active { background-color: ${WIDGET_CONFIG.colors.primary}; }

  .snn-close, .snn-reset-button {
    background: none; border: none;
    font-size: ${WIDGET_CONFIG.menu.closeButtonSize};
    color: ${WIDGET_CONFIG.colors.secondary};
    cursor: pointer;
    line-height: ${WIDGET_CONFIG.typography.lineHeight};
    border-radius: ${WIDGET_CONFIG.button.borderRadius};
    width: ${WIDGET_CONFIG.menu.closeButtonSize};
    height: ${WIDGET_CONFIG.menu.closeButtonSize};
    position: relative;
    display: flex; align-items: center; justify-content: center;
  }
  .snn-close::before { content: '×'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: ${WIDGET_CONFIG.menu.closeButtonSize}; line-height: 1; }
  .snn-reset-button svg { width: 22px; height: 22px; fill: ${WIDGET_CONFIG.colors.secondary}; }
  .snn-close:focus, .snn-reset-button:focus { outline: solid 2px ${WIDGET_CONFIG.colors.secondary}; }
  .snn-close:hover, .snn-reset-button:hover { background: rgba(255,255,255,0.2); }

  .snn-tooltip { position: absolute; bottom: -35px; left: 50%; transform: translateX(-50%); background-color: rgba(0,0,0,0.8); color: white; padding: 6px 10px; border-radius: 4px; font-size: 12px; white-space: nowrap; pointer-events: none; opacity: 0; transition: opacity 0.2s; z-index: 1000; }
  .snn-tooltip::before { content: ''; position: absolute; top: -4px; left: 50%; transform: translateX(-50%); border: 5px solid transparent; border-bottom-color: rgba(0,0,0,0.8); border-top: none; }
  .snn-close:hover .snn-tooltip, .snn-close:focus .snn-tooltip, .snn-reset-button:hover .snn-tooltip, .snn-reset-button:focus .snn-tooltip { opacity: 1; }

  .snn-header { display: flex; align-items: center; padding: 10px; background: ${WIDGET_CONFIG.colors.primary}; height: ${WIDGET_CONFIG.menu.headerHeight}; position: sticky; top: 0; z-index: 10; box-shadow: 0 2px 4px rgba(0,0,0,0.1); gap: 8px; }
  .snn-content { padding: 20px 20px 0 20px; }

  .snn-language-selector { width: 100%; background: white; color: black; border: none; padding: 14px; font-size: 16px; font-family: ${WIDGET_CONFIG.typography.fontFamily}; border-radius: 5px; margin-bottom: 20px; cursor: pointer; outline: none; }
  .snn-language-selector:focus { outline: 2px solid ${WIDGET_CONFIG.colors.primary}; outline-offset: 2px; }

  .snn-options-grid { display: grid; grid-template-columns: ${WIDGET_CONFIG.gridLayout.columns}; gap: ${WIDGET_CONFIG.gridLayout.gap}; margin-bottom: 20px; }

  .snn-title { margin: 0; font-size: ${WIDGET_CONFIG.menu.titleFontSize}; color: ${WIDGET_CONFIG.colors.secondary}; line-height: ${WIDGET_CONFIG.typography.lineHeight} !important; margin-left: 5px; font-weight: ${WIDGET_CONFIG.typography.titleFontWeight}; flex: 1; letter-spacing: 1px !important; word-spacing: 2px !important; text-align: left; }
`;

// ===========================================
// SVG ICONS
// ===========================================

const icons = {
  buttonsvg: `<svg xmlns="http://www.w3.org/2000/svg" style="fill:white;" viewBox="0 0 24 24" width="30px" height="30px"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M20.5 6c-2.61.7-5.67 1-8.5 1s-5.89-.3-8.5-1L3 8c1.86.5 4 .83 6 1v13h2v-6h2v6h2V9c2-.17 4.14-.5 6-1l-.5-2zM12 6c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/></svg>`,
  highContrast: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" version="1.2" viewBox="0 0 35 35"><path fill="currentColor" fill-rule="evenodd" d="M1.9 15.63C1.9 8.05 8.05 1.9 15.63 1.9c7.58 0 13.73 6.15 13.73 13.73 0 .61-.04 1.21-.12 1.79.6.29 1.15.65 1.65 1.07.17-.93.26-1.88.26-2.86C31.15 7.05 24.2.1 15.63.1 7.05.1.1 7.05.1 15.63c0 8.58 6.95 15.53 15.53 15.53 1.22 0 2.41-.14 3.56-.41-.44-.49-.82-1.02-1.14-1.6-.78.14-1.59.21-2.42.21-7.58 0-13.73-6.15-13.73-13.73Z" clip-rule="evenodd"/><path fill="currentColor" fill-rule="evenodd" d="M15.63 1C8.3 1 2.35 6.95 2.35 14.28v2.6c.44.19.87.41 1.27.67V14.28C3.62 7.66 9.01 2.35 15.63 2.35c6.62 0 11.9 5.31 12 11.93-.51-.44-1.06-.81-1.66-1.1C25.3 6.87 21.07 2.35 15.63 2.35Z" clip-rule="evenodd"/><circle cx="25.3" cy="25.11" r="2.44" fill="currentColor"/></svg>`,
  biggerText: `<svg xmlns="http://www.w3.org/2000/svg" version="1.2" viewBox="0 0 36 23"><g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round" stroke-width="2"><path stroke-linejoin="round" d="M26.58 21.32V1m-7.92 4.06V1H34.5v4.06"/><path d="M22.62 21.32h7.92"/><path stroke-linejoin="round" d="M6.78 18.61V5.06M1.5 7.77V5.06h10.56v2.71"/><path d="M4.14 18.61h5.28"/></g></svg>`,
  textSpacing: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15 15" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M4.55 1C4.75 1 4.93 1.12 5.01 1.3L8.01 8.3c.11.25-.01.55-.26.66-.25.11-.55-.01-.66-.26L6.12 6.42H2.99l-.97 2.28c-.11.25-.41.37-.66.26-.25-.11-.37-.41-.26-.66L4.09 1.3C4.17 1.12 4.35 1 4.55 1Zm0 1.77 1.2 2.8H3.35L4.55 2.77ZM11.06 9C11.26 9 11.45 8.88 11.52 8.69l2.75-7c.1-.26-.03-.55-.29-.66-.26-.1-.55.03-.66.28L11.06 7.14 8.77 1.32c-.1-.26-.38-.38-.65-.28-.26.1-.39.38-.28.65L10.59 8.69C10.67 8.88 10.85 9 11.06 9ZM14.95 12.5c0 .11-.04.21-.11.28l-2 2c-.16.16-.41.16-.57 0-.16-.16-.16-.41 0-.57l1.32-1.21H1.52l1.32 1.21c.16.16.16.41 0 .57-.16.16-.41.16-.57 0l-2-2C.2 12.71.15 12.61.15 12.5c0-.11.04-.21.11-.28l2-2c.16-.16.41-.16.57 0 .16.16.16.41 0 .57L1.52 12.1h12.07l-1.32-1.21c-.16-.16-.16-.41 0-.57.16-.16.41-.16.57 0l2 2c.07.07.11.17.11.28Z" fill="#000"/></svg>`,
  pauseAnimations: `<svg xmlns="http://www.w3.org/2000/svg" version="1.2" viewBox="0 0 37 36"><g fill="none" fill-rule="evenodd"><path fill="currentColor" d="M15.81 23.67h-1.27c-.44 0-.8-.36-.8-.8v-9.73c0-.44.36-.8.8-.8h1.27c.44 0 .8.36.8.8v9.73c0 .44-.36.8-.8.8m6.65 0h-1.27c-.44 0-.8-.36-.8-.8v-9.73c0-.44.36-.8.8-.8h1.27c.44 0 .8.36.8.8v9.73c0 .44-.36.8-.8.8"/><path stroke="currentColor" stroke-linecap="round" stroke-width="1.89" d="M18.5 4.78V1m0 34v-3.78M31.72 18H35.5m-34 0h3.78m3.87-9.35L6.48 5.98M30.52 30.02l-2.67-2.67m-.0001-18.7 2.67-2.67M6.48 30.02l2.67-2.67M23.55 5.78l1.44-3.49M12 33.71l1.44-3.49m17.27-7.16 3.49 1.44M2.79 11.5l3.49 1.44m7.15-7.16L12 2.3m13.02 31.41-1.45-3.49m7.15-17.28L34.2 11.49M2.8 24.51l3.49-1.45"/></g></svg>`,
  hideImages: `<svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><path d="M32 12C16 12 4 32 4 32s12 20 28 20 28-20 28-20S48 12 32 12zm0 32a12 12 0 1 1 0-24 12 12 0 0 1 0 24z"/><circle cx="32" cy="32" r="8"/><line x1="8" y1="8" x2="56" y2="56" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg>`,
  dyslexiaFont: `<svg xmlns="http://www.w3.org/2000/svg" version="1.2" viewBox="0 0 31 22"><path fill="currentColor" fill-rule="evenodd" d="M.5 22V1H7.74c6.81 0 11.61 4.34 11.61 10.48C19.35 17.63 14.55 22 7.74 22H.5Zm2.43-4.31h4.81c5.37 0 9.15-2.86 9.15-7.27 0-4.38-3.78-7.24-9.15-7.24H2.93V17.69ZM26.27 4.03l.01 2.17h4.01V8.25h-4l.45 13.75h-3.54L23.78 8.25h-2.42V6.2h2.56l.07-2.17C24.07 1.68 25.6 0 27.7 0c.99 0 1.98.37 2.8 1.01l-.96 1.68c-.35-.37-.95-.64-1.63-.64-.88 0-1.64.83-1.64 1.99Z"/></svg>`,
  biggerCursor: `<svg xmlns="http://www.w3.org/2000/svg" version="1.2" viewBox="0 0 27 27"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16 11.55 9.53 9.53-4.45 4.45-9.53-9.53-4.05 9.06L1 1l24.06 6.5z"/></svg>`,
  lineHeight: `<svg xmlns="http://www.w3.org/2000/svg" version="1.2" viewBox="0 0 47 25"><g fill="none" fill-rule="evenodd"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M4 2.71v20"/><path fill="currentColor" d="m.17 20.53 3.44 4.21c.17.21.49.25.7.07a.5.5 0 0 0 .07-.07l3.44-4.21c.17-.21.14-.53-.07-.7a.5.5 0 0 0-.32-.11H.56a.5.5 0 0 0-.5.5c0 .12.04.23.11.32Zm0-16.33L3.61.18c.17-.21.49-.25.7-.07a.5.5 0 0 1 .07.07l3.44 4.21c.17.21.14.53-.07.7A.5.5 0 0 1 7.44 5.2H.56a.5.5 0 0 1-.5-.5c0-.12.04-.23.11-.32Z"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.5 1.71h30m-30 7h30m-30 7h30m-30 7h24"/></g></svg>`,
  textAlign: `<svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><path d="M10 16h44v4H10zm0 12h44v4H10zm0 12h44v4H10zm0 12h44v4H10z"/></svg>`,
  screenReader: `<svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><path d="M16 24 L24 24 L32 16 L32 48 L24 40 L16 40 Z" fill="currentColor"/><path d="M36 20 C42 24, 42 40, 36 44" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M36 12 C48 24, 48 40, 36 52" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>`,
  resetAll: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 17" width="100%" height="100%"><g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round" stroke-width="1.84"><path d="M16.2 8c0 .97-.19 1.89-.53 2.73-.34.84-.84 1.59-1.45 2.22-.61.64-1.34 1.15-2.15 1.5-.81.35-1.7.55-2.64.55-.94 0-1.83-.2-2.64-.55-.81-.35-1.54-.87-2.15-1.5M2.65 8c0-.97.19-1.89.53-2.73.34-.84.84-1.59 1.45-2.22.61-.64 1.34-1.15 2.15-1.5C7.6 1.2 8.49 1 9.43 1c.94 0 1.83.2 2.64.55.81.35 1.54.87 2.15 1.5"/><path stroke-linejoin="round" d="m4.93 6.96-2.49 1.48L1 5.87m13.01 2.94 2.39-1.65L18 9.63"/></g></svg>`,
  voiceControl: `<svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect x="24" y="8" width="16" height="28" rx="8" fill="currentColor"/><path d="M16 32a16 16 0 0 0 32 0" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><line x1="32" y1="48" x2="32" y2="56" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><line x1="24" y1="56" x2="40" y2="56" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>`,
  fontSelection: `<svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><text x="10" y="45" font-family="serif" font-size="36" fill="currentColor">Aa</text></svg>`,
  colorFilter: `<svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><circle cx="22" cy="32" r="14" fill="#e44" opacity="0.7"/><circle cx="42" cy="32" r="14" fill="#44e" opacity="0.7"/><circle cx="32" cy="20" r="14" fill="#4e4" opacity="0.7"/></svg>`,
  saturation: `<svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><defs><linearGradient id="sg" x1="0" x2="1"><stop offset="0%" stop-color="#aaa"/><stop offset="100%" stop-color="#e44"/></linearGradient></defs><rect x="8" y="20" width="48" height="24" rx="4" fill="url(#sg)"/></svg>`,
};

// ===========================================
// SHADOW DOM SETUP
// ===========================================

let shadowRoot = null;

function injectPageStyles() {
  // CSS is now generated and injected by PHP via wp_add_inline_style
  // We only need to inject the SVG filters here
  const svgFilters = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  svgFilters.style.cssText = 'position:absolute;width:0;height:0;overflow:hidden;';
  svgFilters.setAttribute('aria-hidden', 'true');
  svgFilters.innerHTML = `<defs>
    <filter id="protanopia-filter"><feColorMatrix type="matrix" values="0.567,0.433,0,0,0 0.558,0.442,0,0,0 0,0.242,0.758,0,0 0,0,0,1,0"/></filter>
    <filter id="deuteranopia-filter"><feColorMatrix type="matrix" values="0.625,0.375,0,0,0 0.7,0.3,0,0,0 0,0.3,0.7,0,0 0,0,0,1,0"/></filter>
    <filter id="tritanopia-filter"><feColorMatrix type="matrix" values="0.95,0.05,0,0,0 0,0.433,0.567,0,0 0,0.475,0.525,0,0 0,0,0,1,0"/></filter>
  </defs>`;
  document.body.appendChild(svgFilters);
}

function createShadowContainer() {
  const container = document.createElement('div');
  container.id = 'snn-accessibility-widget-container';
  document.body.appendChild(container);
  shadowRoot = container.attachShadow({ mode: 'open' });
  const styleElement = document.createElement('style');
  styleElement.textContent = widgetStyles;
  shadowRoot.appendChild(styleElement);
  return shadowRoot;
}

// ===========================================
// CORE STATE & UTILITIES
// ===========================================

const domCache = {
  get body() { return document.body; },
  get documentElement() { return document.documentElement; },
  _images: null,
  _imgTs: 0,
  getImages() {
    const now = Date.now();
    if (!this._images || now - this._imgTs > 5000) {
      this._images = document.querySelectorAll('img');
      this._imgTs = now;
    }
    return this._images;
  }
};

function removeSnnClasses(el, prefix) {
  [...el.classList].filter(c => c.startsWith(prefix)).forEach(c => el.classList.remove(c));
}

function applySettings() {
  if (!domCache.body) return;

  // Bigger cursor
  domCache.body.classList.toggle('snn-bigger-cursor', localStorage.getItem('biggerCursor') === 'true');
  // Dyslexia font
  domCache.body.classList.toggle('snn-dyslexia-font', localStorage.getItem('dyslexiaFont') === 'true');
  // Pause animations
  domCache.body.classList.toggle('snn-pause-animations', localStorage.getItem('pauseAnimations') === 'true');

  // Cycled classes
  const cycled = [
    { key: 'biggerText',   el: domCache.body,            prefix: 'snn-bigger-text-',    vals: ['medium','large','xlarge'] },
    { key: 'highContrast', el: domCache.body,            prefix: 'snn-high-contrast-',  vals: ['medium','high','ultra'] },
    { key: 'textSpacing',  el: domCache.body,            prefix: 'snn-text-spacing-',   vals: ['light','medium','heavy'] },
    { key: 'lineHeight',   el: domCache.body,            prefix: 'snn-line-height-',    vals: ['2em','3em','4em'] },
    { key: 'textAlign',    el: domCache.body,            prefix: 'snn-text-align-',     vals: ['left','center','right'] },
    { key: 'fontSelection',el: domCache.body,            prefix: 'snn-font-',           vals: ['arial','times','verdana'] },
    { key: 'colorFilter',  el: domCache.documentElement, prefix: 'snn-filter-',         vals: ['protanopia','deuteranopia','tritanopia','grayscale'] },
    { key: 'saturation',   el: domCache.documentElement, prefix: 'snn-saturation-',     vals: ['low','high','none'] },
  ];

  cycled.forEach(({ key, el, prefix, vals }) => {
    removeSnnClasses(el, prefix);
    const val = localStorage.getItem(key);
    if (val && vals.includes(val)) el.classList.add(prefix + val);
  });

  // Hide images
  const hide = localStorage.getItem('hideImages') === 'true';
  domCache.getImages().forEach(img => { img.style.display = hide ? 'none' : ''; });

  if (screenReader.active && screenReader.isSupported) {
    document.addEventListener('focusin', screenReader.handleFocus);
  }
  if (voiceControl.isActive && voiceControl.isSupported) {
    voiceControl.startListening();
  }
}

// ===========================================
// FEATURE HANDLERS
// ===========================================

function toggleHideImages(isActive) {
  domCache.getImages().forEach(img => { img.style.display = isActive ? 'none' : ''; });
}

function makeCycler(key, values, bodyTarget = true) {
  return function () {
    const el     = bodyTarget ? domCache.body : domCache.documentElement;
    const prefix = {
      biggerText:    'snn-bigger-text-',
      highContrast:  'snn-high-contrast-',
      textSpacing:   'snn-text-spacing-',
      lineHeight:    'snn-line-height-',
      textAlign:     'snn-text-align-',
      fontSelection: 'snn-font-',
      colorFilter:   'snn-filter-',
      saturation:    'snn-saturation-',
    }[key];
    const current   = localStorage.getItem(key);
    const currIdx   = values.indexOf(current);
    const nextIdx   = (currIdx + 1) % (values.length + 1);
    removeSnnClasses(el, prefix);
    if (nextIdx < values.length) {
      const next = values[nextIdx];
      localStorage.setItem(key, next);
      el.classList.add(prefix + next);
    } else {
      localStorage.removeItem(key);
    }
    return true;
  };
}

const handleBiggerText    = makeCycler('biggerText',    ['medium','large','xlarge']);
const handleHighContrast  = makeCycler('highContrast',  ['medium','high','ultra']);
const handleTextSpacing   = makeCycler('textSpacing',   ['light','medium','heavy']);
const handleLineHeight    = makeCycler('lineHeight',    ['2em','3em','4em']);
const handleTextAlign     = makeCycler('textAlign',     ['left','center','right']);
const handleFontSelection = makeCycler('fontSelection', ['arial','times','verdana']);
const handleColorFilter   = makeCycler('colorFilter',   ['protanopia','deuteranopia','tritanopia','grayscale'], false);
const handleSaturation    = makeCycler('saturation',    ['low','high','none'], false);

// ===========================================
// SCREEN READER
// ===========================================

const LANG_MAP = { de:'de-DE',es:'es-ES',it:'it-IT',fr:'fr-FR',ru:'ru-RU',tr:'tr-TR',ar:'ar-SA',hi:'hi-IN','zh-cn':'zh-CN',jp:'ja-JP',pt:'pt-PT',bn:'bn-IN',ko:'ko-KR',vi:'vi-VN',id:'id-ID',th:'th-TH',pl:'pl-PL',nl:'nl-NL',el:'el-GR',sv:'sv-SE',no:'no-NO',da:'da-DK',fi:'fi-FI',cs:'cs-CZ',hu:'hu-HU',ro:'ro-RO',he:'he-IL',fa:'fa-IR',ur:'ur-PK' };
function getSpeechLang() { return LANG_MAP[currentLanguage] || 'en-US'; }

const screenReader = {
  active: localStorage.getItem('screenReader') === 'true',
  isSupported: 'speechSynthesis' in window,
  handleFocus(event) {
    if (!screenReader.active || !screenReader.isSupported) return;
    try {
      const content = event.target.innerText || event.target.alt || event.target.title || '';
      if (!content.trim()) return;
      window.speechSynthesis.cancel();
      const u = new SpeechSynthesisUtterance(content);
      u.lang = getSpeechLang();
      u.onerror = e => console.warn('SR error:', e.error);
      window.speechSynthesis.speak(u);
    } catch (e) { console.warn('SR error:', e); }
  },
  toggle(isActive) {
    if (!screenReader.isSupported) return false;
    screenReader.active = isActive;
    localStorage.setItem('screenReader', isActive);
    try {
      if (isActive) {
        document.addEventListener('focusin', screenReader.handleFocus);
        const u = new SpeechSynthesisUtterance(getTranslation('screenReaderOn'));
        u.lang = getSpeechLang();
        window.speechSynthesis.speak(u);
      } else {
        document.removeEventListener('focusin', screenReader.handleFocus);
        window.speechSynthesis.cancel();
      }
    } catch (e) { return false; }
    return true;
  },
};

// ===========================================
// VOICE CONTROL
// ===========================================

const voiceControl = {
  isActive: localStorage.getItem('voiceControl') === 'true',
  recognition: null,
  isSupported: ('SpeechRecognition' in window || 'webkitSpeechRecognition' in window),
  retryCount: 0,
  maxRetries: 3,
  toggle(isActive) {
    if (!voiceControl.isSupported) return false;
    voiceControl.isActive = isActive;
    localStorage.setItem('voiceControl', isActive);
    try {
      if (isActive) { voiceControl.startListening(); }
      else { voiceControl.recognition && voiceControl.recognition.stop(); voiceControl.recognition = null; voiceControl.retryCount = 0; }
    } catch (e) { return false; }
    return true;
  },
  startListening() {
    if (!voiceControl.isSupported) return;
    try {
      const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
      voiceControl.recognition = new SR();
      voiceControl.recognition.lang = getSpeechLang();
      voiceControl.recognition.interimResults = false;
      voiceControl.recognition.continuous = false;
      voiceControl.recognition.onstart = () => { voiceControl.retryCount = 0; };
      voiceControl.recognition.onresult = e => {
        try { voiceControl.handleVoiceCommand(e.results[0][0].transcript.toLowerCase()); } catch (ex) { console.warn('VC error:', ex); }
      };
      voiceControl.recognition.onerror = e => {
        if (e.error === 'no-speech' && voiceControl.retryCount < voiceControl.maxRetries) {
          voiceControl.retryCount++;
          setTimeout(() => voiceControl.isActive && voiceControl.startListening(), 1000);
        }
      };
      voiceControl.recognition.onend = () => {
        if (voiceControl.isActive && voiceControl.retryCount < voiceControl.maxRetries) {
          setTimeout(() => voiceControl.isActive && voiceControl.startListening(), 100);
        }
      };
      voiceControl.recognition.start();
    } catch (e) { console.warn('VC init error:', e); }
  },
  handleVoiceCommand(command) {
    const cmds = VOICE_COMMANDS[currentLanguage] || VOICE_COMMANDS['en'];
    const norm = command.trim().replace(/\s+/g, ' ');

    if (cmds.showMenu.some(c => norm.includes(c))) {
      if (!menuCache.button) menuCache.init();
      menuCache.button && menuCache.button.click();
      return;
    }
    if (cmds.resetAll.some(c => norm.includes(c))) {
      resetAccessibilitySettings();
      return;
    }

    let matched = null;
    for (const [key, phrases] of Object.entries(cmds)) {
      if (key === 'showMenu' || key === 'resetAll') continue;
      if (phrases.some(p => norm.includes(p))) { matched = key; break; }
    }
    if (!matched) return;

    if (!menuCache.menu) menuCache.init();
    const btn = menuCache.menu && (
      menuCache.menu.querySelector(`.snn-accessibility-option[data-key='${matched}']`) ||
      menuCache.menu.querySelector(`.snn-accessibility-option[data-accessibility-option-id='${matched}']`)
    );
    btn && btn.click();
  },
};

// ===========================================
// UI BUILDERS
// ===========================================

function createToggleButton(text, lsKey, className, target = document.body, customFn = null, icon = '', feature = null, optId = null) {
  const btn = document.createElement('button');
  btn.classList.add('snn-accessibility-option');
  btn.setAttribute('role', 'switch');
  btn.setAttribute('aria-label', text);
  btn.setAttribute('data-key', lsKey);
  if (optId) btn.setAttribute('data-accessibility-option-id', optId);
  btn.innerHTML = `<span class="snn-icon">${icon}</span><span class="snn-button-text">${text}</span>`;

  if (feature && !feature.isSupported) {
    btn.disabled = true;
    return btn;
  }

  const sync = () => {
    const on = localStorage.getItem(lsKey) === 'true';
    btn.classList.toggle('active', on);
    btn.setAttribute('aria-pressed', on);
  };
  sync();

  btn.addEventListener('click', () => {
    const newVal = localStorage.getItem(lsKey) !== 'true';
    if (customFn && customFn(newVal) === false) return;
    localStorage.setItem(lsKey, newVal);
    btn.classList.toggle('active', newVal);
    btn.setAttribute('aria-pressed', newVal);
    if (className) target.classList.toggle(className, newVal);
  });

  return btn;
}

function createActionButton(text, actionFn, icon, optsConfig = null, optId = null) {
  const btn = document.createElement('button');
  btn.classList.add('snn-accessibility-option');
  btn.setAttribute('aria-label', text);
  if (optId) { btn.setAttribute('data-accessibility-option-id', optId); btn.setAttribute('data-key', optId); }

  let stepsHtml = '';
  if (optsConfig) {
    stepsHtml = '<div class="snn-option-steps">' + '<div class="snn-option-step"></div>'.repeat(optsConfig.count) + '</div>';
  }
  btn.innerHTML = `<span class="snn-icon">${icon}</span><span class="snn-button-text">${text}</span>${stepsHtml}`;

  const syncSteps = () => {
    if (!optsConfig || !optId) return;
    const maps = {
      biggerText:    ['medium','large','xlarge'],
      highContrast:  ['medium','high','ultra'],
      textSpacing:   ['light','medium','heavy'],
      lineHeight:    ['2em','3em','4em'],
      textAlign:     ['left','center','right'],
      fontSelection: ['arial','times','verdana'],
      colorFilter:   ['protanopia','deuteranopia','tritanopia','grayscale'],
      saturation:    ['low','high','none'],
    };
    const vals = maps[optId] || [];
    const cur  = localStorage.getItem(optId);
    const idx  = vals.indexOf(cur);
    btn.querySelectorAll('.snn-option-step').forEach((s, i) => s.classList.toggle('active', i <= idx));
    btn.classList.toggle('active', idx !== -1);
    btn.setAttribute('aria-pressed', idx !== -1);
  };
  syncSteps();

  btn.addEventListener('click', () => { actionFn(); syncSteps(); });
  return btn;
}

// ===========================================
// RESET
// ===========================================

function resetAccessibilitySettings() {
  const keys = ['biggerCursor','biggerText','dyslexiaFont','hideImages','lineHeight','pauseAnimations','screenReader','textAlign','textSpacing','highContrast','voiceControl','fontSelection','colorFilter','saturation'];
  keys.forEach(k => localStorage.removeItem(k));

  const bodyCls  = ['snn-bigger-cursor','snn-dyslexia-font','snn-pause-animations','snn-bigger-text-medium','snn-bigger-text-large','snn-bigger-text-xlarge','snn-text-spacing-light','snn-text-spacing-medium','snn-text-spacing-heavy','snn-line-height-2em','snn-line-height-3em','snn-line-height-4em','snn-text-align-left','snn-text-align-center','snn-text-align-right','snn-font-arial','snn-font-times','snn-font-verdana','snn-high-contrast-medium','snn-high-contrast-high','snn-high-contrast-ultra'];
  const docCls   = ['snn-filter-protanopia','snn-filter-deuteranopia','snn-filter-tritanopia','snn-filter-grayscale','snn-saturation-low','snn-saturation-high','snn-saturation-none'];

  bodyCls.forEach(c => document.body.classList.remove(c));
  docCls.forEach(c => document.documentElement.classList.remove(c));
  domCache.getImages().forEach(img => { img.style.display = ''; });

  screenReader.active && screenReader.toggle(false);
  voiceControl.isActive && voiceControl.toggle(false);

  shadowRoot.querySelectorAll('.snn-accessibility-option').forEach(b => {
    b.classList.remove('active');
    b.setAttribute('aria-pressed', 'false');
    b.querySelectorAll('.snn-option-step').forEach(s => s.classList.remove('active'));
  });
}

// ===========================================
// MENU BUILD
// ===========================================

const menuCache = { menu: null, button: null, closeButton: null, init() { this.menu = shadowRoot.getElementById('snn-accessibility-menu'); this.button = shadowRoot.getElementById('snn-accessibility-button'); this.closeButton = this.menu && this.menu.querySelector('.snn-close'); } };

function createAccessibilityButton() {
  const wrap = document.createElement('div');
  wrap.id = 'snn-accessibility-fixed-button';
  const btn = document.createElement('button');
  btn.id = 'snn-accessibility-button';
  btn.innerHTML = icons.buttonsvg;
  btn.setAttribute('aria-label', getTranslation('accessibilityMenu'));
  btn.addEventListener('click', toggleMenu);
  btn.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggleMenu(); } });
  wrap.appendChild(btn);
  shadowRoot.appendChild(wrap);
}

function createAccessibilityMenu() {
  const menu = document.createElement('div');
  menu.id = 'snn-accessibility-menu';
  menu.setAttribute('role', 'dialog');
  menu.setAttribute('aria-labelledby', 'snn-accessibility-title');
  menu.setAttribute('aria-hidden', 'true');

  // Header
  const header = document.createElement('div');
  header.className = 'snn-header';

  const title = document.createElement('div');
  title.className = 'snn-title';
  title.id = 'snn-accessibility-title';
  title.textContent = getTranslation('accessibilityTools');

  const resetBtn = document.createElement('button');
  resetBtn.className = 'snn-reset-button';
  resetBtn.innerHTML = `${icons.resetAll}<span class="snn-tooltip">${getTranslation('reset')}</span>`;
  resetBtn.setAttribute('aria-label', getTranslation('resetAllSettings'));
  resetBtn.addEventListener('click', resetAccessibilitySettings);

  const closeBtn = document.createElement('button');
  closeBtn.className = 'snn-close';
  closeBtn.innerHTML = `<span class="snn-tooltip">${getTranslation('close')}</span>`;
  closeBtn.setAttribute('aria-label', getTranslation('closeAccessibilityMenu'));
  closeBtn.addEventListener('click', closeMenu);

  header.append(title, resetBtn, closeBtn);
  menu.appendChild(header);

  // Content
  const content = document.createElement('div');
  content.className = 'snn-content';

  // Language selector
  const langs = [
    {code:'en',name:'English'},{code:'pl',name:'Polski'},{code:'de',name:'Deutsch'},
    {code:'es',name:'Español'},{code:'fr',name:'Français'},{code:'it',name:'Italiano'},
    {code:'ru',name:'Русский'},{code:'tr',name:'Türkçe'},{code:'ar',name:'العربية'},
    {code:'hi',name:'हिन्दी'},{code:'zh-cn',name:'简体中文'},{code:'jp',name:'日本語'},
    {code:'pt',name:'Português'},{code:'ko',name:'한국어'},{code:'nl',name:'Nederlands'},
    {code:'sv',name:'Svenska'},
  ];
  const langSel = document.createElement('select');
  langSel.className = 'snn-language-selector';
  langSel.setAttribute('aria-label', getTranslation('selectLanguage'));
  langs.forEach(l => {
    const o = document.createElement('option');
    o.value = l.code; o.textContent = l.name;
    if (l.code === currentLanguage) o.selected = true;
    langSel.appendChild(o);
  });
  langSel.addEventListener('change', e => { if (setLanguage(e.target.value)) updateMenuLanguage(); });
  content.appendChild(langSel);

  // Grid
  const grid = document.createElement('div');
  grid.className = 'snn-options-grid';

  const cfg = WIDGET_CONFIG;
  const buttons = [
    { order:1,  type:'action', text:getTranslation('textSize'),       fn:handleBiggerText,    icon:icons.biggerText,   enabled:cfg.enableBiggerText,      opts:{count:3}, id:'biggerText' },
    { order:2,  type:'action', text:getTranslation('highContrast'),   fn:handleHighContrast,  icon:icons.highContrast, enabled:cfg.enableHighContrast,    opts:{count:3}, id:'highContrast' },
    { order:3,  type:'action', text:getTranslation('textAlign'),      fn:handleTextAlign,     icon:icons.textAlign,    enabled:cfg.enableTextAlign,       opts:{count:3}, id:'textAlign' },
    { order:4,  type:'action', text:getTranslation('colorFilter'),    fn:handleColorFilter,   icon:icons.colorFilter,  enabled:cfg.enableColorFilter,     opts:{count:4}, id:'colorFilter' },
    { order:5,  type:'action', text:getTranslation('textSpacing'),    fn:handleTextSpacing,   icon:icons.textSpacing,  enabled:cfg.enableTextSpacing,     opts:{count:3}, id:'textSpacing' },
    { order:6,  type:'action', text:getTranslation('lineHeight'),     fn:handleLineHeight,    icon:icons.lineHeight,   enabled:cfg.enableLineHeight,      opts:{count:3}, id:'lineHeight' },
    { order:7,  type:'action', text:getTranslation('fontSelection'),  fn:handleFontSelection, icon:icons.fontSelection,enabled:cfg.enableFontSelection,   opts:{count:3}, id:'fontSelection' },
    { order:8,  type:'action', text:getTranslation('saturation'),     fn:handleSaturation,    icon:icons.saturation,   enabled:cfg.enableSaturation,      opts:{count:3}, id:'saturation' },
    { order:9,  type:'toggle', text:getTranslation('dyslexiaFriendly'),key:'dyslexiaFont',   cls:'snn-dyslexia-font',  icon:icons.dyslexiaFont,  enabled:cfg.enableDyslexiaFont,   id:'dyslexiaFont' },
    { order:10, type:'toggle', text:getTranslation('biggerCursor'),   key:'biggerCursor',    cls:'snn-bigger-cursor',  icon:icons.biggerCursor,  enabled:cfg.enableBiggerCursor,   id:'biggerCursor' },
    { order:11, type:'toggle', text:getTranslation('hideImages'),     key:'hideImages',      cls:null, customFn:toggleHideImages, icon:icons.hideImages, enabled:cfg.enableHideImages, id:'hideImages' },
    { order:12, type:'toggle', text:getTranslation('pauseAnimations'),key:'pauseAnimations', cls:'snn-pause-animations',icon:icons.pauseAnimations,enabled:cfg.enablePauseAnimations, id:'pauseAnimations' },
    { order:98, type:'toggle', text:getTranslation('screenReader'),   key:'screenReader',    cls:null, customFn:screenReader.toggle, icon:icons.screenReader, feature:screenReader, enabled:cfg.enableScreenReader, id:'screenReader' },
    { order:99, type:'toggle', text:getTranslation('voiceCommand'),   key:'voiceControl',   cls:null, customFn:voiceControl.toggle, icon:icons.voiceControl, feature:voiceControl, enabled:cfg.enableVoiceControl, id:'voiceControl' },
  ];

  buttons
    .filter(b => b.enabled)
    .sort((a, b) => a.order - b.order)
    .forEach(b => {
      const el = b.type === 'action'
        ? createActionButton(b.text, b.fn, b.icon, b.opts, b.id)
        : createToggleButton(b.text, b.key, b.cls, document.body, b.customFn, b.icon, b.feature, b.id);
      if (el) grid.appendChild(el);
    });

  content.appendChild(grid);
  menu.appendChild(content);
  shadowRoot.appendChild(menu);
}

function updateMenuLanguage() {
  const menu = shadowRoot.getElementById('snn-accessibility-menu');
  if (!menu) return;
  const wasOpen = menu.style.display === 'block';
  menu.remove();
  menuCache.menu = null; menuCache.closeButton = null;
  createAccessibilityMenu();
  const mainBtn = shadowRoot.getElementById('snn-accessibility-button');
  if (mainBtn) mainBtn.setAttribute('aria-label', getTranslation('accessibilityMenu'));
  if (wasOpen) { menuCache.init(); openMenu(); }
}

// ===========================================
// MENU STATE
// ===========================================

function toggleMenu() {
  if (!menuCache.menu) menuCache.init();
  menuCache.menu.style.display === 'block' ? closeMenu() : openMenu();
}

function openMenu() {
  if (!menuCache.menu) menuCache.init();
  menuCache.menu.style.display = 'block';
  menuCache.menu.setAttribute('aria-hidden', 'false');
  const first = menuCache.menu.querySelector('.snn-accessibility-option');
  (first || menuCache.closeButton) && (first || menuCache.closeButton).focus();
  document.addEventListener('keydown', handleMenuKeyboard);
}

function closeMenu() {
  if (!menuCache.menu) menuCache.init();
  menuCache.menu.style.display = 'none';
  menuCache.menu.setAttribute('aria-hidden', 'true');
  menuCache.button && menuCache.button.focus();
  document.removeEventListener('keydown', handleMenuKeyboard);
}

function handleMenuKeyboard(e) {
  if (!menuCache.menu || menuCache.menu.style.display !== 'block') return;
  if (e.key === 'Escape') { e.preventDefault(); closeMenu(); return; }
  const all = [...menuCache.menu.querySelectorAll('button, [href], input, select, [tabindex]:not([tabindex="-1"])')];
  if (e.key === 'Tab') {
    if (e.shiftKey && shadowRoot.activeElement === all[0]) { e.preventDefault(); all[all.length-1].focus(); }
    else if (!e.shiftKey && shadowRoot.activeElement === all[all.length-1]) { e.preventDefault(); all[0].focus(); }
  }
  if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
    e.preventDefault();
    const opts = [...menuCache.menu.querySelectorAll('.snn-accessibility-option, .snn-close, .snn-reset-button')];
    const cur  = opts.indexOf(shadowRoot.activeElement);
    const next = e.key === 'ArrowDown' ? (cur+1) % opts.length : (cur-1+opts.length) % opts.length;
    opts[next].focus();
  }
}

// ===========================================
// INIT
// ===========================================

function initAccessibilityWidget() {
  createShadowContainer();
  injectPageStyles();
  applySettings();
  createAccessibilityButton();
  createAccessibilityMenu();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initAccessibilityWidget);
} else {
  initAccessibilityWidget();
}
