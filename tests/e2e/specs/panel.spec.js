import { expect, test } from '../helpers/fixtures.js'

/*
 * The panel of a Filament application with Tagixo installed: what it offers is
 * what the installed builders bring, and a record opens the editor of the core.
 */

test('the navigation shows what the installed builders bring', async ({ page }) => {
    await page.goto('/admin')

    // Asserted on the links and not on what is on screen: Filament's sidebar
    // groups are collapsible, so a section can be offered and folded away.
    const sidebar = page.locator('.fi-main-sidebar')

    for (const section of ['pages', 'popups', 'global-blocks', 'forms', 'mails', 'documents', 'sliders', 'menus', 'media-gallery', 'theme-builder']) {
        await expect(sidebar.locator(`a[href$="/admin/${section}"]`), section).toHaveCount(1)
    }

    // Layouts are reached from the Theme Builder, so they are not in the menu.
    await expect(sidebar.locator('a[href$="/admin/layouts"]')).toHaveCount(0)
})

test('a listing shows the records of its type', async ({ page, seed }) => {
    await page.goto('/admin/pages')

    await expect(page.getByText(seed.page.slug)).toBeVisible()
    await expect(page.getByText('E2E taken slug')).toBeVisible()
})

test('a row opens the builder of the core, and comes back to the listing', async ({ page, seed }) => {
    await page.goto('/admin/pages')

    await page.getByRole('link', { name: /E2E home/ }).first().click()
    await page.waitForURL(new RegExp(`tagixo/builder/embed\\?type=pages&id=${seed.page.id}`))

    // The editor carries the way back to the listing it was opened from.
    expect(page.url()).toContain(encodeURIComponent('/admin/pages'))
})

test('the record CRUD of the core is off, and the editor is not', async ({ page, seed }) => {
    // Asked of the server rather than navigated to: a 404 in the page would be
    // a console error, and this suite refuses those.
    const manage = await page.request.get('/tagixo/manage')
    const editor = await page.request.get(`/tagixo/builder/embed?type=pages&id=${seed.page.id}`)

    // The panel administers records; the editor is still the core's own mount.
    expect(manage.status()).toBe(404)
    expect(editor.status()).toBe(200)
})
