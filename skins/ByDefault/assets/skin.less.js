const
  params = new URLSearchParams(document.location.search.replace(/^\?/, '')),
  env = params.has('dev') || params.get('v').match(/\./g).length > 2
    ? 'development'
    : 'production';
less = {
    env: env,
    logLevel: 2,
    async: false,
    fileAsync: false,
    poll: 1000,
    functions: {},
    dumpLineNumbers: 'comments',
    relativeUrls: false,
    // rootpath: ":/a.com/"
};
