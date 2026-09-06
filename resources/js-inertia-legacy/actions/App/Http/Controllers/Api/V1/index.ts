import AuthTokenController from './AuthTokenController'
import ContextController from './ContextController'
import Ar from './Ar'
import Ap from './Ap'
import Gl from './Gl'
import DomainsController from './DomainsController'
import ReportsController from './ReportsController'
const V1 = {
    AuthTokenController: Object.assign(AuthTokenController, AuthTokenController),
ContextController: Object.assign(ContextController, ContextController),
Ar: Object.assign(Ar, Ar),
Ap: Object.assign(Ap, Ap),
Gl: Object.assign(Gl, Gl),
DomainsController: Object.assign(DomainsController, DomainsController),
ReportsController: Object.assign(ReportsController, ReportsController),
}

export default V1