import { expect, test } from '../helpers/fixtures.js'

/*
 * The panel's Preview is a link to the page's own public URL, signed so an
 * unpublished one can be seen before it is published. Nothing in the panel can
 * tell whether the application serves that URL at all — the package leaves
 * routing to the application on purpose — so the chain is worth walking whole:
 * the action's href, the signature, and the page that comes back.
 */

test('the preview of a page opens the page itself', async ({ page, seed }) => {
    await page.goto('/admin/pages')

    const row = page.locator('.fi-ta-row', { hasText: seed.page.slug })
    const preview = row.getByRole('link', { name: /preview/i })

    await expect(preview).toHaveCount(1)

    const href = await preview.getAttribute('href')

    expect(href).toContain(`/${seed.page.slug}`)
    expect(href).toContain('_preview=1')
    expect(href).toContain('signature=')

    await page.goto(href)

    await expect(page.getByText('E2E page heading')).toBeVisible()
})
