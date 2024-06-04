const params = new URLSearchParams(document.location.search.replace(/^\?/, ''));
less = {
    env: params.has('dev') < 0 && params.get('v').match(/\./g).length < 3 ? 'production' : 'development',
    logLevel: 2,
    async: false,
    fileAsync: false,
    poll: 1000,
    functions: {},
    dumpLineNumbers: 'comments',
    relativeUrls: false,
    // rootpath: ":/a.com/"
};
