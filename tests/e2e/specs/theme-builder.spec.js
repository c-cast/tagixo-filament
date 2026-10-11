import { expect, test } from '../helpers/fixtures.js'

/*
 * The Theme Builder: a template dressed zone by zone. The header belongs to the
 * template, so it opens the layout itself.
 */

test('a template shows its three zones, and the header opens the layout', async ({ page, seed }) => {
    await page.goto('/admin/theme-builder')

    await expect(page.getByText(seed.layout.name)).toBeVisible()

    const zones = page.locator('article')
    await expect(zones).toHaveCount(3)
    await expect(zones.nth(0)).toContainText('Header')
    // The header was seeded, the footer was not.
    await expect(zones.nth(0)).toContainText('Built')
    await expect(zones.nth(2)).toContainText('Empty')

    await zones.nth(0).getByRole('link').click()
    await page.waitForURL(new RegExp(`type=layouts&id=${seed.layout.id}`))
    expect(page.url()).toContain('scope=header')
})

test('the footer of that template opens empty, on the template itself', async ({ page, seed }) => {
    await page.goto('/admin/theme-builder')

    await page.locator('article').nth(2).getByRole('link').click()
    await page.waitForURL(/scope=footer/)

    // Same record, the other document.
    expect(page.url()).toContain(`type=layouts&id=${seed.layout.id}`)
})

test('a new template is asked what it claims, right away', async ({ page }) => {
    await page.goto('/admin/theme-builder')
    await page.getByRole('link', { name: /New template/i }).click()
    await page.waitForURL(/\/admin\/layouts\/create/)

    // The five plain questions, on the screen that creates a template: one that
    // claims nothing dresses nothing.
    for (const label of ['Every page', 'The homepage', 'Specific pages', 'Everything of these content types', 'Archive of these content types']) {
        await expect(page.getByText(label, { exact: true }).first(), label).toBeVisible()
    }
})
