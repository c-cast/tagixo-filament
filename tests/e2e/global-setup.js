import { execFileSync } from 'node:child_process'
import { existsSync, mkdirSync } from 'node:fs'
import { resolve } from 'node:path'
import { SANDBOX_DIR, SEED_FILE, STATE_DIR } from './support/paths.js'

/**
 * Rebuilds the sandbox's e2e database and seeds the records the specs drive.
 * Everything runs with APP_ENV=e2e, so the sandbox's own database is never
 * touched; the seeder writes the ids to `.state/seed.json`, which the `seed`
 * fixture reads.
 */
export default async function globalSetup() {
    if (!existsSync(resolve(SANDBOX_DIR, '.env.e2e'))) {
        throw new Error(`Missing ${SANDBOX_DIR}/.env.e2e — see the README of this package.`)
    }

    mkdirSync(STATE_DIR, { recursive: true })

    const env = { ...process.env, APP_ENV: 'e2e', TAGIXO_E2E_SEED_FILE: SEED_FILE }
    const php = (args) => execFileSync('php', args, { cwd: SANDBOX_DIR, env, stdio: 'inherit' })

    php(['artisan', 'vendor:publish', '--tag=tagixo-assets', '--force'])
    php(['artisan', 'optimize:clear'])
    php(['artisan', 'migrate:fresh', '--force', '--seed', '--seeder=Database\\Seeders\\E2eSeeder'])
}
