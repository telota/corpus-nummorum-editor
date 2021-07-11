export default {

    install(Vue, options) {

        Vue.prototype.$editor_format = {
            
            typeName: (id, mint) => {
                if (id) {
                    return 'cn.' + (!mint ? 'unknown' : (mint.split('/').pop().toLowerCase())) + '.' + id
                } else {
                    return 'Unknown Type'
                }
            },
    
            coinName: (id, mint) => {
                if (id) {
                    return (!mint ? 'unknown' : mint) + ' CN_' + id
                } else {
                    return 'Unknown Coin'
                }
            },
    
            date: (language, date, giveminutes) => {
    
                const year      = date ? date.substring(0,4)    : '----'
                const month     = date ? date.substring(5,7)    : '--'
                const day       = date ? date.substring(8,10)   : '--'
                const hour      = date && giveminutes ? date.substring(11,13)   : '--'
                const minutes   = date && giveminutes ? date.substring(14,16)   : '--'
                    
                if (language === 'de') {
                    return day + '.' + month + '.' + year + (giveminutes ? (', ' + hour + ':' + minutes) : '')
                } 
                else {
                    return month + '/' + day + '/' + year + (giveminutes ? (', ' + hour + ':' + minutes) : '')
                }
            },
    
            number: (language, number) => {
                if (parseInt(number)) {

                    number = String(number).split('').reverse().join('')
                    const delimiter = language === 'de' ? '.' : ','
                    
                    const splits = []
    
                    for (let i = 0; i <= number.length; i = i + 3) {
                        const to_push = number.substr(i, (i+3)).split('').reverse().join('')
                        if(to_push) { splits.push(to_push)} 
                    }
    
                    return splits.reverse().join(delimiter)
                }
                else {
                    return number
                }
            }
        }
    }
}