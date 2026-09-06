import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:25
 * @route '/api/v1/gl/accounts'
 */
export const accountsIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: accountsIndex.url(options),
    method: 'get',
})

accountsIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/gl/accounts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:25
 * @route '/api/v1/gl/accounts'
 */
accountsIndex.url = (options?: RouteQueryOptions) => {
    return accountsIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:25
 * @route '/api/v1/gl/accounts'
 */
accountsIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: accountsIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:25
 * @route '/api/v1/gl/accounts'
 */
accountsIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: accountsIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:25
 * @route '/api/v1/gl/accounts'
 */
    const accountsIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: accountsIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:25
 * @route '/api/v1/gl/accounts'
 */
        accountsIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: accountsIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:25
 * @route '/api/v1/gl/accounts'
 */
        accountsIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: accountsIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    accountsIndex.form = accountsIndexForm
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountsShow
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:32
 * @route '/api/v1/gl/accounts/{account}'
 */
export const accountsShow = (args: { account: string | number } | [account: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: accountsShow.url(args, options),
    method: 'get',
})

accountsShow.definition = {
    methods: ["get","head"],
    url: '/api/v1/gl/accounts/{account}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountsShow
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:32
 * @route '/api/v1/gl/accounts/{account}'
 */
accountsShow.url = (args: { account: string | number } | [account: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { account: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    account: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        account: args.account,
                }

    return accountsShow.definition.url
            .replace('{account}', parsedArgs.account.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountsShow
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:32
 * @route '/api/v1/gl/accounts/{account}'
 */
accountsShow.get = (args: { account: string | number } | [account: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: accountsShow.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountsShow
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:32
 * @route '/api/v1/gl/accounts/{account}'
 */
accountsShow.head = (args: { account: string | number } | [account: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: accountsShow.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountsShow
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:32
 * @route '/api/v1/gl/accounts/{account}'
 */
    const accountsShowForm = (args: { account: string | number } | [account: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: accountsShow.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountsShow
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:32
 * @route '/api/v1/gl/accounts/{account}'
 */
        accountsShowForm.get = (args: { account: string | number } | [account: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: accountsShow.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountsShow
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:32
 * @route '/api/v1/gl/accounts/{account}'
 */
        accountsShowForm.head = (args: { account: string | number } | [account: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: accountsShow.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    accountsShow.form = accountsShowForm
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:39
 * @route '/api/v1/gl/journals'
 */
export const journalsIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: journalsIndex.url(options),
    method: 'get',
})

journalsIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/gl/journals',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:39
 * @route '/api/v1/gl/journals'
 */
journalsIndex.url = (options?: RouteQueryOptions) => {
    return journalsIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:39
 * @route '/api/v1/gl/journals'
 */
journalsIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: journalsIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:39
 * @route '/api/v1/gl/journals'
 */
journalsIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: journalsIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:39
 * @route '/api/v1/gl/journals'
 */
    const journalsIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: journalsIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:39
 * @route '/api/v1/gl/journals'
 */
        journalsIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: journalsIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:39
 * @route '/api/v1/gl/journals'
 */
        journalsIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: journalsIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    journalsIndex.form = journalsIndexForm
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsStore
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:46
 * @route '/api/v1/gl/journals'
 */
export const journalsStore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: journalsStore.url(options),
    method: 'post',
})

journalsStore.definition = {
    methods: ["post"],
    url: '/api/v1/gl/journals',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsStore
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:46
 * @route '/api/v1/gl/journals'
 */
journalsStore.url = (options?: RouteQueryOptions) => {
    return journalsStore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsStore
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:46
 * @route '/api/v1/gl/journals'
 */
journalsStore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: journalsStore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsStore
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:46
 * @route '/api/v1/gl/journals'
 */
    const journalsStoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: journalsStore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsStore
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:46
 * @route '/api/v1/gl/journals'
 */
        journalsStoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: journalsStore.url(options),
            method: 'post',
        })
    
    journalsStore.form = journalsStoreForm
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsShow
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:73
 * @route '/api/v1/gl/journals/{journal}'
 */
export const journalsShow = (args: { journal: string | number } | [journal: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: journalsShow.url(args, options),
    method: 'get',
})

journalsShow.definition = {
    methods: ["get","head"],
    url: '/api/v1/gl/journals/{journal}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsShow
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:73
 * @route '/api/v1/gl/journals/{journal}'
 */
journalsShow.url = (args: { journal: string | number } | [journal: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { journal: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    journal: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        journal: args.journal,
                }

    return journalsShow.definition.url
            .replace('{journal}', parsedArgs.journal.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsShow
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:73
 * @route '/api/v1/gl/journals/{journal}'
 */
journalsShow.get = (args: { journal: string | number } | [journal: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: journalsShow.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsShow
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:73
 * @route '/api/v1/gl/journals/{journal}'
 */
journalsShow.head = (args: { journal: string | number } | [journal: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: journalsShow.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsShow
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:73
 * @route '/api/v1/gl/journals/{journal}'
 */
    const journalsShowForm = (args: { journal: string | number } | [journal: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: journalsShow.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsShow
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:73
 * @route '/api/v1/gl/journals/{journal}'
 */
        journalsShowForm.get = (args: { journal: string | number } | [journal: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: journalsShow.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsShow
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:73
 * @route '/api/v1/gl/journals/{journal}'
 */
        journalsShowForm.head = (args: { journal: string | number } | [journal: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: journalsShow.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    journalsShow.form = journalsShowForm
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsPost
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:80
 * @route '/api/v1/gl/journals/{journal}/post'
 */
export const journalsPost = (args: { journal: string | number } | [journal: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: journalsPost.url(args, options),
    method: 'post',
})

journalsPost.definition = {
    methods: ["post"],
    url: '/api/v1/gl/journals/{journal}/post',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsPost
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:80
 * @route '/api/v1/gl/journals/{journal}/post'
 */
journalsPost.url = (args: { journal: string | number } | [journal: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { journal: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    journal: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        journal: args.journal,
                }

    return journalsPost.definition.url
            .replace('{journal}', parsedArgs.journal.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsPost
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:80
 * @route '/api/v1/gl/journals/{journal}/post'
 */
journalsPost.post = (args: { journal: string | number } | [journal: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: journalsPost.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsPost
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:80
 * @route '/api/v1/gl/journals/{journal}/post'
 */
    const journalsPostForm = (args: { journal: string | number } | [journal: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: journalsPost.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalsPost
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:80
 * @route '/api/v1/gl/journals/{journal}/post'
 */
        journalsPostForm.post = (args: { journal: string | number } | [journal: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: journalsPost.url(args, options),
            method: 'post',
        })
    
    journalsPost.form = journalsPostForm
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalLinesIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:88
 * @route '/api/v1/gl/journal-lines'
 */
export const journalLinesIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: journalLinesIndex.url(options),
    method: 'get',
})

journalLinesIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/gl/journal-lines',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalLinesIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:88
 * @route '/api/v1/gl/journal-lines'
 */
journalLinesIndex.url = (options?: RouteQueryOptions) => {
    return journalLinesIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalLinesIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:88
 * @route '/api/v1/gl/journal-lines'
 */
journalLinesIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: journalLinesIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalLinesIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:88
 * @route '/api/v1/gl/journal-lines'
 */
journalLinesIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: journalLinesIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalLinesIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:88
 * @route '/api/v1/gl/journal-lines'
 */
    const journalLinesIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: journalLinesIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalLinesIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:88
 * @route '/api/v1/gl/journal-lines'
 */
        journalLinesIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: journalLinesIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::journalLinesIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:88
 * @route '/api/v1/gl/journal-lines'
 */
        journalLinesIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: journalLinesIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    journalLinesIndex.form = journalLinesIndexForm
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::trialBalance
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:95
 * @route '/api/v1/gl/trial-balance'
 */
export const trialBalance = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: trialBalance.url(options),
    method: 'get',
})

trialBalance.definition = {
    methods: ["get","head"],
    url: '/api/v1/gl/trial-balance',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::trialBalance
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:95
 * @route '/api/v1/gl/trial-balance'
 */
trialBalance.url = (options?: RouteQueryOptions) => {
    return trialBalance.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::trialBalance
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:95
 * @route '/api/v1/gl/trial-balance'
 */
trialBalance.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: trialBalance.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::trialBalance
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:95
 * @route '/api/v1/gl/trial-balance'
 */
trialBalance.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: trialBalance.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::trialBalance
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:95
 * @route '/api/v1/gl/trial-balance'
 */
    const trialBalanceForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: trialBalance.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::trialBalance
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:95
 * @route '/api/v1/gl/trial-balance'
 */
        trialBalanceForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: trialBalance.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::trialBalance
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:95
 * @route '/api/v1/gl/trial-balance'
 */
        trialBalanceForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: trialBalance.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    trialBalance.form = trialBalanceForm
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::generalLedger
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:103
 * @route '/api/v1/gl/general-ledger'
 */
export const generalLedger = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: generalLedger.url(options),
    method: 'get',
})

generalLedger.definition = {
    methods: ["get","head"],
    url: '/api/v1/gl/general-ledger',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::generalLedger
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:103
 * @route '/api/v1/gl/general-ledger'
 */
generalLedger.url = (options?: RouteQueryOptions) => {
    return generalLedger.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::generalLedger
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:103
 * @route '/api/v1/gl/general-ledger'
 */
generalLedger.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: generalLedger.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::generalLedger
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:103
 * @route '/api/v1/gl/general-ledger'
 */
generalLedger.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: generalLedger.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::generalLedger
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:103
 * @route '/api/v1/gl/general-ledger'
 */
    const generalLedgerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: generalLedger.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::generalLedger
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:103
 * @route '/api/v1/gl/general-ledger'
 */
        generalLedgerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: generalLedger.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::generalLedger
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:103
 * @route '/api/v1/gl/general-ledger'
 */
        generalLedgerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: generalLedger.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    generalLedger.form = generalLedgerForm
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountBalances
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:110
 * @route '/api/v1/gl/account-balances'
 */
export const accountBalances = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: accountBalances.url(options),
    method: 'get',
})

accountBalances.definition = {
    methods: ["get","head"],
    url: '/api/v1/gl/account-balances',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountBalances
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:110
 * @route '/api/v1/gl/account-balances'
 */
accountBalances.url = (options?: RouteQueryOptions) => {
    return accountBalances.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountBalances
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:110
 * @route '/api/v1/gl/account-balances'
 */
accountBalances.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: accountBalances.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountBalances
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:110
 * @route '/api/v1/gl/account-balances'
 */
accountBalances.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: accountBalances.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountBalances
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:110
 * @route '/api/v1/gl/account-balances'
 */
    const accountBalancesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: accountBalances.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountBalances
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:110
 * @route '/api/v1/gl/account-balances'
 */
        accountBalancesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: accountBalances.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::accountBalances
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:110
 * @route '/api/v1/gl/account-balances'
 */
        accountBalancesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: accountBalances.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    accountBalances.form = accountBalancesForm
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::periodsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:118
 * @route '/api/v1/gl/periods'
 */
export const periodsIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: periodsIndex.url(options),
    method: 'get',
})

periodsIndex.definition = {
    methods: ["get","head"],
    url: '/api/v1/gl/periods',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::periodsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:118
 * @route '/api/v1/gl/periods'
 */
periodsIndex.url = (options?: RouteQueryOptions) => {
    return periodsIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::periodsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:118
 * @route '/api/v1/gl/periods'
 */
periodsIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: periodsIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::periodsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:118
 * @route '/api/v1/gl/periods'
 */
periodsIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: periodsIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::periodsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:118
 * @route '/api/v1/gl/periods'
 */
    const periodsIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: periodsIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::periodsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:118
 * @route '/api/v1/gl/periods'
 */
        periodsIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: periodsIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Api\V1\Gl\GlController::periodsIndex
 * @see app/Http/Controllers/Api/V1/Gl/GlController.php:118
 * @route '/api/v1/gl/periods'
 */
        periodsIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: periodsIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    periodsIndex.form = periodsIndexForm
const GlController = { accountsIndex, accountsShow, journalsIndex, journalsStore, journalsShow, journalsPost, journalLinesIndex, trialBalance, generalLedger, accountBalances, periodsIndex }

export default GlController