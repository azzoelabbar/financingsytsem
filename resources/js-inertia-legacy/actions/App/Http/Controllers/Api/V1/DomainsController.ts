import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:24
 * @route '/api/v1/investments'
 */
export const investmentsIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: investmentsIndex.url(options),
    method: 'get',
})

investmentsIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/investments',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:24
 * @route '/api/v1/investments'
 */
investmentsIndex.url = (options?: RouteQueryOptions) => {
    return investmentsIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:24
 * @route '/api/v1/investments'
 */
investmentsIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: investmentsIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:24
 * @route '/api/v1/investments'
 */
investmentsIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: investmentsIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:24
 * @route '/api/v1/investments'
 */
    const investmentsIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: investmentsIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:24
 * @route '/api/v1/investments'
 */
        investmentsIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: investmentsIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:24
 * @route '/api/v1/investments'
 */
        investmentsIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: investmentsIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    investmentsIndex.form = investmentsIndexForm
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:31
 * @route '/api/v1/investments'
 */
export const investmentsStore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: investmentsStore.url(options),
    method: 'post',
})

investmentsStore.definition = {
    methods: ["post"],
    url: '/api/v1/investments',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:31
 * @route '/api/v1/investments'
 */
investmentsStore.url = (options?: RouteQueryOptions) => {
    return investmentsStore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:31
 * @route '/api/v1/investments'
 */
investmentsStore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: investmentsStore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:31
 * @route '/api/v1/investments'
 */
    const investmentsStoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: investmentsStore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:31
 * @route '/api/v1/investments'
 */
        investmentsStoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: investmentsStore.url(options),
            method: 'post',
        })
    
    investmentsStore.form = investmentsStoreForm
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsShow
 * @see app/Http/Controllers/Api/V1/DomainsController.php:47
 * @route '/api/v1/investments/{investment}'
 */
export const investmentsShow = (args: { investment: string | number } | [investment: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: investmentsShow.url(args, options),
    method: 'get',
})

investmentsShow.definition = {
    methods: ["get","head"],
    url: '/api/v1/investments/{investment}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsShow
 * @see app/Http/Controllers/Api/V1/DomainsController.php:47
 * @route '/api/v1/investments/{investment}'
 */
investmentsShow.url = (args: { investment: string | number } | [investment: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { investment: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    investment: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        investment: args.investment,
                }

    return investmentsShow.definition.url
            .replace('{investment}', parsedArgs.investment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsShow
 * @see app/Http/Controllers/Api/V1/DomainsController.php:47
 * @route '/api/v1/investments/{investment}'
 */
investmentsShow.get = (args: { investment: string | number } | [investment: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: investmentsShow.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsShow
 * @see app/Http/Controllers/Api/V1/DomainsController.php:47
 * @route '/api/v1/investments/{investment}'
 */
investmentsShow.head = (args: { investment: string | number } | [investment: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: investmentsShow.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsShow
 * @see app/Http/Controllers/Api/V1/DomainsController.php:47
 * @route '/api/v1/investments/{investment}'
 */
    const investmentsShowForm = (args: { investment: string | number } | [investment: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: investmentsShow.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsShow
 * @see app/Http/Controllers/Api/V1/DomainsController.php:47
 * @route '/api/v1/investments/{investment}'
 */
        investmentsShowForm.get = (args: { investment: string | number } | [investment: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: investmentsShow.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::investmentsShow
 * @see app/Http/Controllers/Api/V1/DomainsController.php:47
 * @route '/api/v1/investments/{investment}'
 */
        investmentsShowForm.head = (args: { investment: string | number } | [investment: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: investmentsShow.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    investmentsShow.form = investmentsShowForm
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:54
 * @route '/api/v1/expenses'
 */
export const expensesIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: expensesIndex.url(options),
    method: 'get',
})

expensesIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/expenses',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:54
 * @route '/api/v1/expenses'
 */
expensesIndex.url = (options?: RouteQueryOptions) => {
    return expensesIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:54
 * @route '/api/v1/expenses'
 */
expensesIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: expensesIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:54
 * @route '/api/v1/expenses'
 */
expensesIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: expensesIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:54
 * @route '/api/v1/expenses'
 */
    const expensesIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: expensesIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:54
 * @route '/api/v1/expenses'
 */
        expensesIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: expensesIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:54
 * @route '/api/v1/expenses'
 */
        expensesIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: expensesIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    expensesIndex.form = expensesIndexForm
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:61
 * @route '/api/v1/expenses'
 */
export const expensesStore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: expensesStore.url(options),
    method: 'post',
})

expensesStore.definition = {
    methods: ["post"],
    url: '/api/v1/expenses',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:61
 * @route '/api/v1/expenses'
 */
expensesStore.url = (options?: RouteQueryOptions) => {
    return expensesStore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:61
 * @route '/api/v1/expenses'
 */
expensesStore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: expensesStore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:61
 * @route '/api/v1/expenses'
 */
    const expensesStoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: expensesStore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:61
 * @route '/api/v1/expenses'
 */
        expensesStoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: expensesStore.url(options),
            method: 'post',
        })
    
    expensesStore.form = expensesStoreForm
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesSubmit
 * @see app/Http/Controllers/Api/V1/DomainsController.php:81
 * @route '/api/v1/expenses/{expense}/submit'
 */
export const expensesSubmit = (args: { expense: string | number } | [expense: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: expensesSubmit.url(args, options),
    method: 'post',
})

expensesSubmit.definition = {
    methods: ["post"],
    url: '/api/v1/expenses/{expense}/submit',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesSubmit
 * @see app/Http/Controllers/Api/V1/DomainsController.php:81
 * @route '/api/v1/expenses/{expense}/submit'
 */
expensesSubmit.url = (args: { expense: string | number } | [expense: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { expense: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    expense: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        expense: args.expense,
                }

    return expensesSubmit.definition.url
            .replace('{expense}', parsedArgs.expense.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesSubmit
 * @see app/Http/Controllers/Api/V1/DomainsController.php:81
 * @route '/api/v1/expenses/{expense}/submit'
 */
expensesSubmit.post = (args: { expense: string | number } | [expense: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: expensesSubmit.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesSubmit
 * @see app/Http/Controllers/Api/V1/DomainsController.php:81
 * @route '/api/v1/expenses/{expense}/submit'
 */
    const expensesSubmitForm = (args: { expense: string | number } | [expense: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: expensesSubmit.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesSubmit
 * @see app/Http/Controllers/Api/V1/DomainsController.php:81
 * @route '/api/v1/expenses/{expense}/submit'
 */
        expensesSubmitForm.post = (args: { expense: string | number } | [expense: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: expensesSubmit.url(args, options),
            method: 'post',
        })
    
    expensesSubmit.form = expensesSubmitForm
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesApprove
 * @see app/Http/Controllers/Api/V1/DomainsController.php:89
 * @route '/api/v1/expenses/{expense}/approve'
 */
export const expensesApprove = (args: { expense: string | number } | [expense: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: expensesApprove.url(args, options),
    method: 'post',
})

expensesApprove.definition = {
    methods: ["post"],
    url: '/api/v1/expenses/{expense}/approve',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesApprove
 * @see app/Http/Controllers/Api/V1/DomainsController.php:89
 * @route '/api/v1/expenses/{expense}/approve'
 */
expensesApprove.url = (args: { expense: string | number } | [expense: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { expense: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    expense: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        expense: args.expense,
                }

    return expensesApprove.definition.url
            .replace('{expense}', parsedArgs.expense.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesApprove
 * @see app/Http/Controllers/Api/V1/DomainsController.php:89
 * @route '/api/v1/expenses/{expense}/approve'
 */
expensesApprove.post = (args: { expense: string | number } | [expense: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: expensesApprove.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesApprove
 * @see app/Http/Controllers/Api/V1/DomainsController.php:89
 * @route '/api/v1/expenses/{expense}/approve'
 */
    const expensesApproveForm = (args: { expense: string | number } | [expense: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: expensesApprove.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesApprove
 * @see app/Http/Controllers/Api/V1/DomainsController.php:89
 * @route '/api/v1/expenses/{expense}/approve'
 */
        expensesApproveForm.post = (args: { expense: string | number } | [expense: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: expensesApprove.url(args, options),
            method: 'post',
        })
    
    expensesApprove.form = expensesApproveForm
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesPost
 * @see app/Http/Controllers/Api/V1/DomainsController.php:97
 * @route '/api/v1/expenses/{expense}/post'
 */
export const expensesPost = (args: { expense: string | number } | [expense: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: expensesPost.url(args, options),
    method: 'post',
})

expensesPost.definition = {
    methods: ["post"],
    url: '/api/v1/expenses/{expense}/post',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesPost
 * @see app/Http/Controllers/Api/V1/DomainsController.php:97
 * @route '/api/v1/expenses/{expense}/post'
 */
expensesPost.url = (args: { expense: string | number } | [expense: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { expense: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    expense: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        expense: args.expense,
                }

    return expensesPost.definition.url
            .replace('{expense}', parsedArgs.expense.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesPost
 * @see app/Http/Controllers/Api/V1/DomainsController.php:97
 * @route '/api/v1/expenses/{expense}/post'
 */
expensesPost.post = (args: { expense: string | number } | [expense: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: expensesPost.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesPost
 * @see app/Http/Controllers/Api/V1/DomainsController.php:97
 * @route '/api/v1/expenses/{expense}/post'
 */
    const expensesPostForm = (args: { expense: string | number } | [expense: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: expensesPost.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::expensesPost
 * @see app/Http/Controllers/Api/V1/DomainsController.php:97
 * @route '/api/v1/expenses/{expense}/post'
 */
        expensesPostForm.post = (args: { expense: string | number } | [expense: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: expensesPost.url(args, options),
            method: 'post',
        })
    
    expensesPost.form = expensesPostForm
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:105
 * @route '/api/v1/projects'
 */
export const projectsIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: projectsIndex.url(options),
    method: 'get',
})

projectsIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/projects',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:105
 * @route '/api/v1/projects'
 */
projectsIndex.url = (options?: RouteQueryOptions) => {
    return projectsIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:105
 * @route '/api/v1/projects'
 */
projectsIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: projectsIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:105
 * @route '/api/v1/projects'
 */
projectsIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: projectsIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:105
 * @route '/api/v1/projects'
 */
    const projectsIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: projectsIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:105
 * @route '/api/v1/projects'
 */
        projectsIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: projectsIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:105
 * @route '/api/v1/projects'
 */
        projectsIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: projectsIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    projectsIndex.form = projectsIndexForm
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:112
 * @route '/api/v1/projects'
 */
export const projectsStore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: projectsStore.url(options),
    method: 'post',
})

projectsStore.definition = {
    methods: ["post"],
    url: '/api/v1/projects',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:112
 * @route '/api/v1/projects'
 */
projectsStore.url = (options?: RouteQueryOptions) => {
    return projectsStore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:112
 * @route '/api/v1/projects'
 */
projectsStore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: projectsStore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:112
 * @route '/api/v1/projects'
 */
    const projectsStoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: projectsStore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:112
 * @route '/api/v1/projects'
 */
        projectsStoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: projectsStore.url(options),
            method: 'post',
        })
    
    projectsStore.form = projectsStoreForm
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsCharge
 * @see app/Http/Controllers/Api/V1/DomainsController.php:124
 * @route '/api/v1/projects/{project}/charge'
 */
export const projectsCharge = (args: { project: string | number } | [project: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: projectsCharge.url(args, options),
    method: 'post',
})

projectsCharge.definition = {
    methods: ["post"],
    url: '/api/v1/projects/{project}/charge',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsCharge
 * @see app/Http/Controllers/Api/V1/DomainsController.php:124
 * @route '/api/v1/projects/{project}/charge'
 */
projectsCharge.url = (args: { project: string | number } | [project: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { project: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    project: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        project: args.project,
                }

    return projectsCharge.definition.url
            .replace('{project}', parsedArgs.project.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsCharge
 * @see app/Http/Controllers/Api/V1/DomainsController.php:124
 * @route '/api/v1/projects/{project}/charge'
 */
projectsCharge.post = (args: { project: string | number } | [project: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: projectsCharge.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsCharge
 * @see app/Http/Controllers/Api/V1/DomainsController.php:124
 * @route '/api/v1/projects/{project}/charge'
 */
    const projectsChargeForm = (args: { project: string | number } | [project: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: projectsCharge.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsCharge
 * @see app/Http/Controllers/Api/V1/DomainsController.php:124
 * @route '/api/v1/projects/{project}/charge'
 */
        projectsChargeForm.post = (args: { project: string | number } | [project: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: projectsCharge.url(args, options),
            method: 'post',
        })
    
    projectsCharge.form = projectsChargeForm
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsCapitalize
 * @see app/Http/Controllers/Api/V1/DomainsController.php:136
 * @route '/api/v1/projects/{project}/capitalize'
 */
export const projectsCapitalize = (args: { project: string | number } | [project: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: projectsCapitalize.url(args, options),
    method: 'post',
})

projectsCapitalize.definition = {
    methods: ["post"],
    url: '/api/v1/projects/{project}/capitalize',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsCapitalize
 * @see app/Http/Controllers/Api/V1/DomainsController.php:136
 * @route '/api/v1/projects/{project}/capitalize'
 */
projectsCapitalize.url = (args: { project: string | number } | [project: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { project: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    project: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        project: args.project,
                }

    return projectsCapitalize.definition.url
            .replace('{project}', parsedArgs.project.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsCapitalize
 * @see app/Http/Controllers/Api/V1/DomainsController.php:136
 * @route '/api/v1/projects/{project}/capitalize'
 */
projectsCapitalize.post = (args: { project: string | number } | [project: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: projectsCapitalize.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsCapitalize
 * @see app/Http/Controllers/Api/V1/DomainsController.php:136
 * @route '/api/v1/projects/{project}/capitalize'
 */
    const projectsCapitalizeForm = (args: { project: string | number } | [project: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: projectsCapitalize.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::projectsCapitalize
 * @see app/Http/Controllers/Api/V1/DomainsController.php:136
 * @route '/api/v1/projects/{project}/capitalize'
 */
        projectsCapitalizeForm.post = (args: { project: string | number } | [project: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: projectsCapitalize.url(args, options),
            method: 'post',
        })
    
    projectsCapitalize.form = projectsCapitalizeForm
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::taxCodesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:148
 * @route '/api/v1/tax/codes'
 */
export const taxCodesIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: taxCodesIndex.url(options),
    method: 'get',
})

taxCodesIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/tax/codes',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::taxCodesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:148
 * @route '/api/v1/tax/codes'
 */
taxCodesIndex.url = (options?: RouteQueryOptions) => {
    return taxCodesIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::taxCodesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:148
 * @route '/api/v1/tax/codes'
 */
taxCodesIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: taxCodesIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::taxCodesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:148
 * @route '/api/v1/tax/codes'
 */
taxCodesIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: taxCodesIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\DomainsController::taxCodesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:148
 * @route '/api/v1/tax/codes'
 */
    const taxCodesIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: taxCodesIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::taxCodesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:148
 * @route '/api/v1/tax/codes'
 */
        taxCodesIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: taxCodesIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::taxCodesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:148
 * @route '/api/v1/tax/codes'
 */
        taxCodesIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: taxCodesIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    taxCodesIndex.form = taxCodesIndexForm
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::taxCodesStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:155
 * @route '/api/v1/tax/codes'
 */
export const taxCodesStore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: taxCodesStore.url(options),
    method: 'post',
})

taxCodesStore.definition = {
    methods: ["post"],
    url: '/api/v1/tax/codes',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::taxCodesStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:155
 * @route '/api/v1/tax/codes'
 */
taxCodesStore.url = (options?: RouteQueryOptions) => {
    return taxCodesStore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::taxCodesStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:155
 * @route '/api/v1/tax/codes'
 */
taxCodesStore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: taxCodesStore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\DomainsController::taxCodesStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:155
 * @route '/api/v1/tax/codes'
 */
    const taxCodesStoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: taxCodesStore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::taxCodesStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:155
 * @route '/api/v1/tax/codes'
 */
        taxCodesStoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: taxCodesStore.url(options),
            method: 'post',
        })
    
    taxCodesStore.form = taxCodesStoreForm
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::openingBalancesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:168
 * @route '/api/v1/opening-balances'
 */
export const openingBalancesIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: openingBalancesIndex.url(options),
    method: 'get',
})

openingBalancesIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/opening-balances',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::openingBalancesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:168
 * @route '/api/v1/opening-balances'
 */
openingBalancesIndex.url = (options?: RouteQueryOptions) => {
    return openingBalancesIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::openingBalancesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:168
 * @route '/api/v1/opening-balances'
 */
openingBalancesIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: openingBalancesIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::openingBalancesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:168
 * @route '/api/v1/opening-balances'
 */
openingBalancesIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: openingBalancesIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\DomainsController::openingBalancesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:168
 * @route '/api/v1/opening-balances'
 */
    const openingBalancesIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: openingBalancesIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::openingBalancesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:168
 * @route '/api/v1/opening-balances'
 */
        openingBalancesIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: openingBalancesIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::openingBalancesIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:168
 * @route '/api/v1/opening-balances'
 */
        openingBalancesIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: openingBalancesIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    openingBalancesIndex.form = openingBalancesIndexForm
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::openingBalancesStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:175
 * @route '/api/v1/opening-balances'
 */
export const openingBalancesStore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: openingBalancesStore.url(options),
    method: 'post',
})

openingBalancesStore.definition = {
    methods: ["post"],
    url: '/api/v1/opening-balances',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::openingBalancesStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:175
 * @route '/api/v1/opening-balances'
 */
openingBalancesStore.url = (options?: RouteQueryOptions) => {
    return openingBalancesStore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::openingBalancesStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:175
 * @route '/api/v1/opening-balances'
 */
openingBalancesStore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: openingBalancesStore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\DomainsController::openingBalancesStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:175
 * @route '/api/v1/opening-balances'
 */
    const openingBalancesStoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: openingBalancesStore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::openingBalancesStore
 * @see app/Http/Controllers/Api/V1/DomainsController.php:175
 * @route '/api/v1/opening-balances'
 */
        openingBalancesStoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: openingBalancesStore.url(options),
            method: 'post',
        })
    
    openingBalancesStore.form = openingBalancesStoreForm
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::booksIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:194
 * @route '/api/v1/books'
 */
export const booksIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: booksIndex.url(options),
    method: 'get',
})

booksIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/books',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::booksIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:194
 * @route '/api/v1/books'
 */
booksIndex.url = (options?: RouteQueryOptions) => {
    return booksIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::booksIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:194
 * @route '/api/v1/books'
 */
booksIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: booksIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::booksIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:194
 * @route '/api/v1/books'
 */
booksIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: booksIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\DomainsController::booksIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:194
 * @route '/api/v1/books'
 */
    const booksIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: booksIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::booksIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:194
 * @route '/api/v1/books'
 */
        booksIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: booksIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::booksIndex
 * @see app/Http/Controllers/Api/V1/DomainsController.php:194
 * @route '/api/v1/books'
 */
        booksIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: booksIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    booksIndex.form = booksIndexForm
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::booksReconcile
 * @see app/Http/Controllers/Api/V1/DomainsController.php:201
 * @route '/api/v1/books/reconcile'
 */
export const booksReconcile = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: booksReconcile.url(options),
    method: 'get',
})

booksReconcile.definition = {
    methods: ["get","head"],
    url: '/api/v1/books/reconcile',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::booksReconcile
 * @see app/Http/Controllers/Api/V1/DomainsController.php:201
 * @route '/api/v1/books/reconcile'
 */
booksReconcile.url = (options?: RouteQueryOptions) => {
    return booksReconcile.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\DomainsController::booksReconcile
 * @see app/Http/Controllers/Api/V1/DomainsController.php:201
 * @route '/api/v1/books/reconcile'
 */
booksReconcile.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: booksReconcile.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\DomainsController::booksReconcile
 * @see app/Http/Controllers/Api/V1/DomainsController.php:201
 * @route '/api/v1/books/reconcile'
 */
booksReconcile.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: booksReconcile.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\DomainsController::booksReconcile
 * @see app/Http/Controllers/Api/V1/DomainsController.php:201
 * @route '/api/v1/books/reconcile'
 */
    const booksReconcileForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: booksReconcile.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::booksReconcile
 * @see app/Http/Controllers/Api/V1/DomainsController.php:201
 * @route '/api/v1/books/reconcile'
 */
        booksReconcileForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: booksReconcile.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\DomainsController::booksReconcile
 * @see app/Http/Controllers/Api/V1/DomainsController.php:201
 * @route '/api/v1/books/reconcile'
 */
        booksReconcileForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: booksReconcile.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    booksReconcile.form = booksReconcileForm
const DomainsController = { investmentsIndex, investmentsStore, investmentsShow, expensesIndex, expensesStore, expensesSubmit, expensesApprove, expensesPost, projectsIndex, projectsStore, projectsCharge, projectsCapitalize, taxCodesIndex, taxCodesStore, openingBalancesIndex, openingBalancesStore, booksIndex, booksReconcile }

export default DomainsController