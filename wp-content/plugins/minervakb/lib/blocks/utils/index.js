export const getOption = (key, defaultValue = null) => window.MinervaKB.settings[key] || defaultValue;

export const pluginUrl = (path) => window.MinervaKB.pluginUrl + path;