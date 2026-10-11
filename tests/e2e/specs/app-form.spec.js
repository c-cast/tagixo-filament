import { expect, test } from '../helpers/fixtures.js'

/*
 * A form whose target is the application: the one a panel renders as its own
 * form, which is what the Preview of the builder opens here.
 */

test('an app form is previewed as a Filament form, not as HTML of its own', async ({ page, seed }) => {
    await page.goto(`/admin/forms/${seed.form.id}/preview`)

    // A real Filament field, built from the saved schema by the mapper — not
    // the HTML the public page would render.
    await expect(page.locator('.fi-fo-field').first()).toBeVisible()
    await expect(page.getByText('E2E e-mail')).toBeVisible()
    await expect(page.locator('input.fi-input').first()).toBeVisible()
})

test('the media library opens, with the folder and the files an upload needs', async ({ page }) => {
    await page.goto('/admin/media-gallery')

    await expect(page.locator('.fi-main')).toBeVisible()
    await expect(page.getByText(/Media/i).first()).toBeVisible()
})
