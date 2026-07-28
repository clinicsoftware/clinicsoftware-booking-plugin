function scopePlugin () {
  const wrapper = '.hello2-forms-app'

  return {
    postcssPlugin: 'postcss-scope-wrapper',
    Rule (rule) {
      // Don't touch keyframes or other at-rules without selectors
      if (rule.parent && rule.parent.type === 'atrule' && /keyframes/i.test(rule.parent.name)) {
        return
      }

      rule.selectors = rule.selectors.map(selector => {
        const s = selector.trim()

        // Scope body/html rules onto the wrapper so they don't leak to the WP admin
        if (s === 'body' || s.startsWith('body')) {
          return wrapper + s.slice(4)
        }

        if (s === 'html' || s.startsWith('html')) {
          return wrapper + s.slice(4)
        }

        if (s === ':root') {
          return wrapper
        }

        return wrapper + ' ' + s
      })
    }
  }
}

scopePlugin.postcss = true

module.exports = {
  plugins: [
    require('tailwindcss'),
    require('autoprefixer'),
    scopePlugin()
  ]
}
