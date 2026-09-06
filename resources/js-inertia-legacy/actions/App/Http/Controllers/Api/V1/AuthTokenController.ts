import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\AuthTokenController::store
 * @see app/Http/Controllers/Api/V1/AuthTokenController.php:21
 * @route '/api/v1/auth/token'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/api/v1/auth/token',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\AuthTokenController::store
 * @see app/Http/Controllers/Api/V1/AuthTokenController.php:21
 * @route '/api/v1/auth/token'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\AuthTokenController::store
 * @see app/Http/Controllers/Api/V1/AuthTokenController.php:21
 * @route '/api/v1/auth/token'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\AuthTokenController::store
 * @see app/Http/Controllers/Api/V1/AuthTokenController.php:21
 * @route '/api/v1/auth/token'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\AuthTokenController::store
 * @see app/Http/Controllers/Api/V1/AuthTokenController.php:21
 * @route '/api/v1/auth/token'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Api\V1\AuthTokenController::destroy
 * @see app/Http/Controllers/Api/V1/AuthTokenController.php:49
 * @route '/api/v1/auth/token'
 */
export const destroy = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/api/v1/auth/token',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Api\V1\AuthTokenController::destroy
 * @see app/Http/Controllers/Api/V1/AuthTokenController.php:49
 * @route '/api/v1/auth/token'
 */
destroy.url = (options?: RouteQueryOptions) => {
    return destroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\AuthTokenController::destroy
 * @see app/Http/Controllers/Api/V1/AuthTokenController.php:49
 * @route '/api/v1/auth/token'
 */
destroy.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Api\V1\AuthTokenController::destroy
 * @see app/Http/Controllers/Api/V1/AuthTokenController.php:49
 * @route '/api/v1/auth/token'
 */
    const destroyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\AuthTokenController::destroy
 * @see app/Http/Controllers/Api/V1/AuthTokenController.php:49
 * @route '/api/v1/auth/token'
 */
        destroyForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const AuthTokenController = { store, destroy }

export default AuthTokenController