'use strict'
const merge = require('webpack-merge')
// const prodEnv = require('./prod.env')
const prodEnv = require('./dev.env')

module.exports = merge(prodEnv, {
    NODE_ENV: '"development"',
    API_ROOT: '"http://47.95.255.57:81"',
    FILE_ROOT: '"http://file.zhahehe.com"',
    JD_ROOT: '"http://jd.zhahehe.com"'
})
