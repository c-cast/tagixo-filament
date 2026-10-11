import { readFileSync } from 'node:fs'
import { expect, test as base } from '@playwright/test'
import { SEED_FILE } from '../support/paths.js'

/**
 * `seed`: the ids the sandbox seeder wrote.
 * `browserErrors`: console errors and page errors, asserted empty after each
 * spec — a panel that works while shouting in the console is not working.
 */
const ALLOWED_CONSOLE_ERRORS = [
    // A 422 answered on purpose (a slug already taken) is logged as a failed request.
    /Failed to load resource: the server responded with a status of 422/,
    // Livewire's SPA navigation aborts the fetch of a page you have already
    // left. It belongs to the panel's own navigation, not to anything Tagixo
    // does.
    /Failed to fetch|The user aborted a request/,
]

export const test = base.extend({
    allowedConsoleErrors: [[], { option: true }],

    // eslint-disable-next-line no-empty-pattern
    seed: async ({}, use) => {
        await use(JSON.parse(readFileSync(SEED_FILE, 'utf8')))
    },

    browserErrors: [
        async ({ page, allowedConsoleErrors }, use) => {
            const errors = []
            const allowed = [...ALLOWED_CONSOLE_ERRORS, ...allowedConsoleErrors]

            const keep = (text) => !allowed.some((pattern) => pattern.test(text))

            page.on('pageerror', (error) => {
                if (keep(error.message)) errors.push(`pageerror: ${error.message}`)
            })
            page.on('console', (message) => {
                if (message.type() !== 'error') return
                if (keep(message.text())) errors.push(`console.error: ${message.text()}`)
            })

            await use(errors)

            expect(errors, 'the browser logged errors').toEqual([])
        },
        { auto: true },
    ],
})

export { expect }
