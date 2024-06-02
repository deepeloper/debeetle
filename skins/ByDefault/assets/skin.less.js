less = {
    env: document.location.search.indexOf('&dev=1') < 0 ? 'production' : 'development',
    logLevel: 2,
    async: false,
    fileAsync: false,
    poll: 1000,
    functions: {},
    dumpLineNumbers: 'comments',
    relativeUrls: false,
    // rootpath: ":/a.com/"
};
