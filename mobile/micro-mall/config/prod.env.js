'use strict'
// module.exports = {
//     NODE_ENV: '"production"',
//     API_ROOT: '"//api.zhahehe.com"',
//     FILE_ROOT: '"//file.zhahehe.com"',
//     JD_ROOT: '"http://jd.zhahehe.com"'
// }

const merge = require('webpack-merge')
const prodEnv = require('./prod.env')

module.exports = merge(prodEnv, {
    NODE_ENV: '"production"',
    API_ROOT: '"http://api.zhahehe.com"',
    FILE_ROOT: '"http://file.zhahehe.com"',
    JD_ROOT: '"http://jd.zhahehe.com"'
})
