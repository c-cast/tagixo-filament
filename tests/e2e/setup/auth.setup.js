import { test as setup } from '@playwright/test'
import { AUTH_FILE } from '../support/paths.js'

/** One login for the whole suite: the sandbox's `/dev/login` shortcut. */
setup('log in as the sandbox developer', async ({ page }) => {
    await page.goto('/dev/login')
    await page.waitForURL(/\/admin/)
    await page.context().storageState({ path: AUTH_FILE })
})
