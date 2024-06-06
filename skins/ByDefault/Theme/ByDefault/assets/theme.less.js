less.globalVars = {
  'content-color': '#000',
  'bg-color': '#D4D0C8',
  'bg-lite-color': '#EBEBDB',
  'bg-halflite-color': '#DCDCD0',
  'bg-dark-color': '#A0A088',
  'bg-warning-color': '#FFC',
  'bg-critical-color': '#FCC',
  'border-dark-color': '#808080',
  'border-darkest-color': '#404040',
  'border-lite-color': '#FFF',
  'tab-shadow-color': '#999',
  '@tooltip-color': '#FFF',
  '@tooltip-background-color': '#000',
  '@tooltip-border': '1px solid #FFF',
};
if ('function' === typeof(less.modifyVars)) {
  less.modifyVars(less.globalVars);
}
