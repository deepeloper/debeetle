less.globalVars = {
  'content-color': '#000',
  'bg-color': '#D4D4C8',
  'bg-lite-color': '#EBEBDB',
  'bg-halflite-color': '#DCDCD0',
  'bg-dark-color': '#A0A088',
  'bg-warning-color': '#FFC',
  'bg-critical-color': '#FCC',
  'border-dark-color': '#808080',
  'border-darkest-color': '#404040',
  'border-lite-color': '#FFF',
  'tab-shadow-color': '#999',
};
if ("function" === typeof (less.modifyVars)) {
  less.modifyVars({
    '@content-color': '#000',
    '@bg-color': '#D4D4C8',
    '@bg-lite-color': '#EBEBDB',
    '@bg-halflite-color': '#DCDCD0',
    '@bg-dark-color': '#A0A088',
    '@bg-warning-color': '#FFC',
    '@bg-critical-color': '#FCC',
    '@border-dark-color': '#808080',
    '@border-darkest-color': '#404040',
    '@border-lite-color': '#FFF',
    '@tab-shadow-color': '#999',
  });
}
