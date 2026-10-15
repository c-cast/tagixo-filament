import { expect, test } from '../helpers/fixtures.js'

/*
 * The menu tree is a field of this SDK, and its Blade view asks the field for
 * everything it draws: the link kinds, the pages, the dropdown kinds and the
 * icon sets. A view asking for something the field does not hand it takes the
 * whole edit page down with an undefined variable — which is why the page is
 * opened here, and not only listed as the panel spec does.
 */

test('a menu opens its tree, with the choices the field hands the view', async ({ page, seed }) => {
    await page.goto(`/admin/menus/${seed.menu.id}/edit`)

    await expect(page.getByText('E2E first item')).toBeVisible()

    const tree = page.locator('[data-tagixo-menu-tree]')

    await expect(tree).toHaveCount(1)

    // The choices live in the Alpine config, which the modal draws from when it
    // opens: asserting on the config is asserting on what the field handed over.
    const config = await tree.getAttribute('x-data')

    expect(config).toContain('Mega menu')
    expect(config).toContain('linkTypeOptions')
    expect(config).toContain('lucide')
})
