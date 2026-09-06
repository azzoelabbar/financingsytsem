import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\ContextController::me
 * @see app/Http/Controllers/Api/V1/ContextController.php:26
 * @route '/api/v1/me/context'
 */
export const me = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: me.url(options),
    method: 'get',
})

me.definition = {
    methods: ["get","head"],
    url: '/api/v1/me/context',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\ContextController::me
 * @see app/Http/Controllers/Api/V1/ContextController.php:26
 * @route '/api/v1/me/context'
 */
me.url = (options?: RouteQueryOptions) => {
    return me.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\ContextController::me
 * @see app/Http/Controllers/Api/V1/ContextController.php:26
 * @route '/api/v1/me/context'
 */
me.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: me.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\ContextController::me
 * @see app/Http/Controllers/Api/V1/ContextController.php:26
 * @route '/api/v1/me/context'
 */
me.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: me.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\ContextController::me
 * @see app/Http/Controllers/Api/V1/ContextController.php:26
 * @route '/api/v1/me/context'
 */
    const meForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: me.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\ContextController::me
 * @see app/Http/Controllers/Api/V1/ContextController.php:26
 * @route '/api/v1/me/context'
 */
        meForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: me.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\ContextController::me
 * @see app/Http/Controllers/Api/V1/ContextController.php:26
 * @route '/api/v1/me/context'
 */
        meForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: me.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    me.form = meForm
const ContextController = { me }

export default ContextController