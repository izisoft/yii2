<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace izi\web;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;
use yii\rbac\CheckAccessInterface;
use yii\db\Query;
use yii\web\IdentityInterface;
use yii\web\UserEvent;
use yii\web\Cookie;
use yii\web\ForbiddenHttpException;
 
class Customer extends Component
{
    const EVENT_BEFORE_LOGIN = 'beforeLogin';
    const EVENT_AFTER_LOGIN = 'afterLogin';
    const EVENT_BEFORE_LOGOUT = 'beforeLogout';
    const EVENT_AFTER_LOGOUT = 'afterLogout';

    /**
     * @var string the class name of the [[identity]] object.
     */
    public $identityClass;
    /**
     * @var boolean whether to enable cookie-based login. Defaults to `false`.
     * Note that this property will be ignored if [[enableSession]] is `false`.
     */
    public $enableAutoLogin = false;
    /**
     * @var boolean whether to use session to persist authentication status across multiple requests.
     * You set this property to be `false` if your application is stateless, which is often the case
     * for RESTful APIs.
     */
    public $enableSession = true;
    /**
     * @var string|array the URL for login when [[loginRequired()]] is called.
     * If an array is given, [[UrlManager::createUrl()]] will be called to create the corresponding URL.
     * The first element of the array should be the route to the login action, and the rest of
     * the name-value pairs are GET parameters used to construct the login URL. For example,
     *
     * ```php
     * ['site/login', 'ref' => 1]
     * ```
     *
     * If this property is `null`, a 403 HTTP exception will be raised when [[loginRequired()]] is called.
     */
    public $loginUrl = ['site/login'];
    /**
     * @var array the configuration of the identity cookie. This property is used only when [[enableAutoLogin]] is `true`.
     * @see Cookie
     */
    public $identityCookie = ['name' => '_identity', 'httpOnly' => true];
    /**
     * @var integer the number of seconds in which the user will be logged out automatically if he
     * remains inactive. If this property is not set, the user will be logged out after
     * the current session expires (c.f. [[Session::timeout]]).
     * Note that this will not work if [[enableAutoLogin]] is `true`.
     */
    public $authTimeout;
    /**
     * @var CheckAccessInterface The access checker to use for checking access.
     * If not set the application auth manager will be used.
     * @since 2.0.9
     */
    public $accessChecker;
    /**
     * @var integer the number of seconds in which the user will be logged out automatically
     * regardless of activity.
     * Note that this will not work if [[enableAutoLogin]] is `true`.
     */
    public $absoluteAuthTimeout;
    /**
     * @var boolean whether to automatically renew the identity cookie each time a page is requested.
     * This property is effective only when [[enableAutoLogin]] is `true`.
     * When this is `false`, the identity cookie will expire after the specified duration since the user
     * is initially logged in. When this is `true`, the identity cookie will expire after the specified duration
     * since the user visits the site the last time.
     * @see enableAutoLogin
     */
    public $autoRenewCookie = true;
    /**
     * @var string the session variable name used to store the value of [[id]].
     */
    public $idParam = '__mid';
    /**
     * @var string the session variable name used to store the value of expiration timestamp of the authenticated state.
     * This is used when [[authTimeout]] is set.
     */
    public $authTimeoutParam = '__mexpire';
    /**
     * @var string the session variable name used to store the value of absolute expiration timestamp of the authenticated state.
     * This is used when [[absoluteAuthTimeout]] is set.
     */
    public $absoluteAuthTimeoutParam = '__mabsoluteExpire';
    /**
     * @var string the session variable name used to store the value of [[returnUrl]].
     */
    public $returnUrlParam = '__mreturnUrl';
    /**
     * @var array MIME types for which this component should redirect to the [[loginUrl]].
     * @since 2.0.8
     */
    public  $type_id;
    
    public $acceptableRedirectTypes = ['text/html', 'application/xhtml+xml'];

    private $_access = [];


    /**
     * Initializes the application component.
     */
    public function init()
    {
    	
        parent::init();
        //var_dump(Yii::$app->controller->module->id); exit;
		$this->loginUrl = ['site/login'];
        if ($this->identityClass === null) {
            throw new InvalidConfigException('Members::identityClass must be set.');
        }
        if ($this->enableAutoLogin && !isset($this->identityCookie['name'])) {
            throw new InvalidConfigException('Members::identityCookie must contain the "name" element.');
        }
        $this->type_id = $this->getTypeID();
    }

    private $_identity = false;

    /**
     * Returns the identity object associated with the currently logged-in user.
     * When [[enableSession]] is true, this method may attempt to read the user's authentication data
     * stored in session and reconstruct the corresponding identity object, if it has not done so before.
     * @param boolean $autoRenew whether to automatically renew authentication status if it has not been done so before.
     * This is only useful when [[enableSession]] is true.
     * @return IdentityInterface|null the identity object associated with the currently logged-in user.
     * `null` is returned if the user is not logged in (not authenticated).
     * @see login()
     * @see logout()
     */
    
    public function getEmail(){
        $u = $this->getIdentity();
        return $u->email;
    }
    
    public function getName(){
        $u = $this->getIdentity();
        if($u->name != ""){
            return $u->name;
        }
        return $u->email;
    }
    
    public function getUsername(){ 
    	$u = $this->getIdentity();
    	if($u->username != ""){
    	    return $u->username;
    	}
    	return $u->email; 
    }
    public function getGender(){
    	$u = $this->getIdentity();    	 
    	return $u->gender;
    }
    
    public function getCode(){
    	$u = $this->getIdentity();
    	return $u->code;
    }
    
    public function getTypeID(){
    	$u = $this->getIdentity();
    
    	return isset($u->type_id) ? $u->type_id : null;
    }
    
    public function getIdentity($autoRenew = true)
    {
        if ($this->_identity === false) {
            if ($this->enableSession && $autoRenew) {
                $this->_identity = null;
                $this->renewAuthStatus();
            } else {
                return null;
            }
        }

        return $this->_identity;
    }

    /**
     * Sets the user identity object.
     *
     * Note that this method does not deal with session or cookie. You should usually use [[switchIdentity()]]
     * to change the identity of the current user.
     *
     * @param IdentityInterface|null $identity the identity object associated with the currently logged user.
     * If null, it means the current user will be a guest without any associated identity.
     * @throws InvalidValueException if `$identity` object does not implement [[IdentityInterface]].
     */
    public function setIdentity($identity)
    {
        if ($identity instanceof IdentityInterface) {
            $this->_identity = $identity;
            $this->_access = [];
        } elseif ($identity === null) {
            $this->_identity = null;
        } else {
            throw new InvalidValueException('The identity object must implement IdentityInterface.');
        }
    }

    /**
     * Logs in a user.
     *
     * After logging in a user, you may obtain the user's identity information from the [[identity]] property.
     * If [[enableSession]] is true, you may even get the identity information in the next requests without
     * calling this method again.
     *
     * The login status is maintained according to the `$duration` parameter:
     *
     * - `$duration == 0`: the identity information will be stored in session and will be available
     *   via [[identity]] as long as the session remains active.
     * - `$duration > 0`: the identity information will be stored in session. If [[enableAutoLogin]] is true,
     *   it will also be stored in a cookie which will expire in `$duration` seconds. As long as
     *   the cookie remains valid or the session is active, you may obtain the user identity information
     *   via [[identity]].
     *
     * Note that if [[enableSession]] is false, the `$duration` parameter will be ignored as it is meaningless
     * in this case.
     *
     * @param IdentityInterface $identity the user identity (which should already be authenticated)
     * @param integer $duration number of seconds that the user can remain in logged-in status.
     * Defaults to 0, meaning login till the user closes the browser or the session is manually destroyed.
     * If greater than 0 and [[enableAutoLogin]] is true, cookie-based login will be supported.
     * Note that if [[enableSession]] is false, this parameter will be ignored.
     * @return boolean whether the user is logged in
     */
    public function login(IdentityInterface $identity, $duration = 0)
    {
        if ($this->beforeLogin($identity, false, $duration)) {
            $this->switchIdentity($identity, $duration);
            $id = $identity->getId();
            $ip = Yii::$app->getRequest()->getUserIP();
            if ($this->enableSession) {
                $log = "Member '$id' logged in from $ip with duration $duration.";
            } else {
                $log = "Member '$id' logged in from $ip. Session not enabled.";
            }
            Yii::info($log, __METHOD__);
            $this->afterLogin($identity, false, $duration);
        }

        return !$this->getIsGuest();
    }

    /**
     * Logs in a user by the given access token.
     * This method will first authenticate the user by calling [[IdentityInterface::findIdentityByAccessToken()]]
     * with the provided access token. If successful, it will call [[login()]] to log in the authenticated user.
     * If authentication fails or [[login()]] is unsuccessful, it will return null.
     * @param string $token the access token
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface|null the identity associated with the given access token. Null is returned if
     * the access token is invalid or [[login()]] is unsuccessful.
     */
    public function loginByAccessToken($token, $type = null)
    {
        /* @var $class IdentityInterface */
        $class = $this->identityClass;
        $identity = $class::findIdentityByAccessToken($token, $type);
        if ($identity && $this->login($identity)) {
            return $identity;
        } else {
            return null;
        }
    }

    /**
     * Logs in a user by cookie.
     *
     * This method attempts to log in a user using the ID and authKey information
     * provided by the [[identityCookie|identity cookie]].
     */
    protected function loginByCookie()
    {
        $data = $this->getIdentityAndDurationFromCookie();
        if (isset($data['identity'], $data['duration'])) {
            $identity = $data['identity'];
            $duration = $data['duration'];
            if ($this->beforeLogin($identity, true, $duration)) {
                $this->switchIdentity($identity, $this->autoRenewCookie ? $duration : 0);
                $id = $identity->getId();
                $ip = Yii::$app->getRequest()->getUserIP();
                Yii::info("User '$id' logged in from $ip via cookie.", __METHOD__);
                $this->afterLogin($identity, true, $duration);
            }
        }
    }

    /**
     * Logs out the current user.
     * This will remove authentication-related session data.
     * If `$destroySession` is true, all session data will be removed.
     * @param boolean $destroySession whether to destroy the whole session. Defaults to true.
     * This parameter is ignored if [[enableSession]] is false.
     * @return boolean whether the user is logged out
     */
    public function logout($destroySession = true)
    {
        $identity = $this->getIdentity();
        if ($identity !== null && $this->beforeLogout($identity)) {
            $this->switchIdentity(null);
            $id = $identity->getId();
            $ip = Yii::$app->getRequest()->getUserIP();
            Yii::info("Member '$id' logged out from $ip.", __METHOD__);
            if ($destroySession && $this->enableSession) {
                Yii::$app->getSession()->destroy();
            }
            $this->afterLogout($identity);
        }

        return $this->getIsGuest();
    }

    /**
     * Returns a value indicating whether the user is a guest (not authenticated).
     * @return boolean whether the current user is a guest.
     * @see getIdentity()
     */
    public function getIsGuest()
    {
        return $this->getIdentity() === null;
    }

    /**
     * Returns a value that uniquely represents the user.
     * @return string|integer the unique identifier for the user. If `null`, it means the user is a guest.
     * @see getIdentity()
     */
    public function getId()
    {
    	$identity = $this->getIdentity();
    
    	return $identity !== null ? $identity->getId() : null;
    }
    public function getPasswordHash()
    {
        $identity = $this->getIdentity();

        return $identity !== null ? $identity->getPasswordHash() : null;
    }
    public function getAuthKey()
    {
    	$identity = $this->getIdentity();
    
    	return $identity !== null ? $identity->getAuthKey() : null;
    }
    public function validatePassword($password)
    {
    	return Yii::$app->security->validatePassword($password, $this->getPasswordHash());
    }
    /**
     * Returns the URL that the browser should be redirected to after successful login.
     *
     * This method reads the return URL from the session. It is usually used by the login action which
     * may call this method to redirect the browser to where it goes after successful authentication.
     *
     * @param string|array $defaultUrl the default return URL in case it was not set previously.
     * If this is null and the return URL was not set previously, [[Application::homeUrl]] will be redirected to.
     * Please refer to [[setReturnUrl()]] on accepted format of the URL.
     * @return string the URL that the user should be redirected to after login.
     * @see loginRequired()
     */
    public function getReturnUrl($defaultUrl = null)
    {
        $url = Yii::$app->getSession()->get($this->returnUrlParam, $defaultUrl);
        if (is_array($url)) {
            if (isset($url[0])) {
                return Yii::$app->getUrlManager()->createUrl($url);
            } else {
                $url = null;
            }
        }

        return $url === null ? Yii::$app->getHomeUrl() : $url;
    }

    /**
     * Remembers the URL in the session so that it can be retrieved back later by [[getReturnUrl()]].
     * @param string|array $url the URL that the user should be redirected to after login.
     * If an array is given, [[UrlManager::createUrl()]] will be called to create the corresponding URL.
     * The first element of the array should be the route, and the rest of
     * the name-value pairs are GET parameters used to construct the URL. For example,
     *
     * ```php
     * ['admin/index', 'ref' => 1]
     * ```
     */
    public function setReturnUrl($url)
    {
        Yii::$app->getSession()->set($this->returnUrlParam, $url);
    }

    /**
     * Redirects the user browser to the login page.
     *
     * Before the redirection, the current URL (if it's not an AJAX url) will be kept as [[returnUrl]] so that
     * the user browser may be redirected back to the current page after successful login.
     *
     * Make sure you set [[loginUrl]] so that the user browser can be redirected to the specified login URL after
     * calling this method.
     *
     * Note that when [[loginUrl]] is set, calling this method will NOT terminate the application execution.
     *
     * @param boolean $checkAjax whether to check if the request is an AJAX request. When this is true and the request
     * is an AJAX request, the current URL (for AJAX request) will NOT be set as the return URL.
     * @param boolean $checkAcceptHeader whether to check if the request accepts HTML responses. Defaults to `true`. When this is true and
     * the request does not accept HTML responses the current URL will not be SET as the return URL. Also instead of
     * redirecting the user an ForbiddenHttpException is thrown. This parameter is available since version 2.0.8.
     * @return Response the redirection response if [[loginUrl]] is set
     * @throws ForbiddenHttpException the "Access Denied" HTTP exception if [[loginUrl]] is not set or a redirect is
     * not applicable.
     * @see checkAcceptHeader
     */
    public function loginRequired($checkAjax = true, $checkAcceptHeader = true)
    {
        $request = Yii::$app->getRequest();
        $canRedirect = !$checkAcceptHeader || $this->checkRedirectAcceptable();
        if ($this->enableSession
            && $request->getIsGet()
            && (!$checkAjax || !$request->getIsAjax())
            && $canRedirect
        ) {
            $this->setReturnUrl($request->getUrl());
        }
        if ($this->loginUrl !== null && $canRedirect) {
            $loginUrl = (array) $this->loginUrl;
            if ($loginUrl[0] !== Yii::$app->requestedRoute) {
                return Yii::$app->getResponse()->redirect($this->loginUrl);
            }
        }
        throw new ForbiddenHttpException(Yii::t('yii', 'Login Required'));
    }

    /**
     * This method is called before logging in a user.
     * The default implementation will trigger the [[EVENT_BEFORE_LOGIN]] event.
     * If you override this method, make sure you call the parent implementation
     * so that the event is triggered.
     * @param IdentityInterface $identity the user identity information
     * @param boolean $cookieBased whether the login is cookie-based
     * @param integer $duration number of seconds that the user can remain in logged-in status.
     * If 0, it means login till the user closes the browser or the session is manually destroyed.
     * @return boolean whether the user should continue to be logged in
     */
    protected function beforeLogin($identity, $cookieBased, $duration)
    {
        $event = new UserEvent([
            'identity' => $identity,
            'cookieBased' => $cookieBased,
            'duration' => $duration,
        ]);
        $this->trigger(self::EVENT_BEFORE_LOGIN, $event);

        return $event->isValid;
    }

    /**
     * This method is called after the user is successfully logged in.
     * The default implementation will trigger the [[EVENT_AFTER_LOGIN]] event.
     * If you override this method, make sure you call the parent implementation
     * so that the event is triggered.
     * @param IdentityInterface $identity the user identity information
     * @param boolean $cookieBased whether the login is cookie-based
     * @param integer $duration number of seconds that the user can remain in logged-in status.
     * If 0, it means login till the user closes the browser or the session is manually destroyed.
     */
    protected function afterLogin($identity, $cookieBased, $duration)
    {
        $this->trigger(self::EVENT_AFTER_LOGIN, new UserEvent([
            'identity' => $identity,
            'cookieBased' => $cookieBased,
            'duration' => $duration,
        ]));
        $user = \common\models\User::find()->where(['id'=>$identity->getId()])->asArray()->one();
        $config = Yii::$app->session['config'];
        $config['adLogin'] = $user;
        
        Yii::$app->session['config'] = $config;
    }

    /**
     * This method is invoked when calling [[logout()]] to log out a user.
     * The default implementation will trigger the [[EVENT_BEFORE_LOGOUT]] event.
     * If you override this method, make sure you call the parent implementation
     * so that the event is triggered.
     * @param IdentityInterface $identity the user identity information
     * @return boolean whether the user should continue to be logged out
     */
    protected function beforeLogout($identity)
    {
        $event = new UserEvent([
            'identity' => $identity,
        ]);
        $this->trigger(self::EVENT_BEFORE_LOGOUT, $event);

        return $event->isValid;
    }

    /**
     * This method is invoked right after a user is logged out via [[logout()]].
     * The default implementation will trigger the [[EVENT_AFTER_LOGOUT]] event.
     * If you override this method, make sure you call the parent implementation
     * so that the event is triggered.
     * @param IdentityInterface $identity the user identity information
     */
    protected function afterLogout($identity)
    {
        $this->trigger(self::EVENT_AFTER_LOGOUT, new UserEvent([
            'identity' => $identity,
        ]));
        Yii::$app->session->destroy();
    }

    /**
     * Renews the identity cookie.
     * This method will set the expiration time of the identity cookie to be the current time
     * plus the originally specified cookie duration.
     */
    protected function renewIdentityCookie()
    {
        $name = $this->identityCookie['name'];
        $value = Yii::$app->getRequest()->getCookies()->getValue($name);
        if ($value !== null) {
            $data = json_decode($value, true);
            if (is_array($data) && isset($data[2])) {
                $cookie = new Cookie($this->identityCookie);
                $cookie->value = $value;
                $cookie->expire = time() + (int) $data[2];
                Yii::$app->getResponse()->getCookies()->add($cookie);
            }
        }
    }

    /**
     * Sends an identity cookie.
     * This method is used when [[enableAutoLogin]] is true.
     * It saves [[id]], [[IdentityInterface::getAuthKey()|auth key]], and the duration of cookie-based login
     * information in the cookie.
     * @param IdentityInterface $identity
     * @param integer $duration number of seconds that the user can remain in logged-in status.
     * @see loginByCookie()
     */
    protected function sendIdentityCookie($identity, $duration)
    {
        $cookie = new Cookie($this->identityCookie);
        $cookie->value = json_encode([
            $identity->getId(),
            $identity->getAuthKey(),
            $duration,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $cookie->expire = time() + $duration;
        Yii::$app->getResponse()->getCookies()->add($cookie);
    }

    /**
     * Determines if an identity cookie has a valid format and contains a valid auth key.
     * This method is used when [[enableAutoLogin]] is true.
     * This method attempts to authenticate a user using the information in the identity cookie.
     * @return array|null Returns an array of 'identity' and 'duration' if valid, otherwise null.
     * @see loginByCookie()
     * @since 2.0.9
     */
    protected function getIdentityAndDurationFromCookie()
    {
        $value = Yii::$app->getRequest()->getCookies()->getValue($this->identityCookie['name']);
        if ($value === null) {
            return null;
        }
        $data = json_decode($value, true);
        if (count($data) == 3) {
            list ($id, $authKey, $duration) = $data;
            /* @var $class IdentityInterface */
            $class = $this->identityClass;
            $identity = $class::findIdentity($id);
            if ($identity !== null) {
                if (!$identity instanceof IdentityInterface) {
                    throw new InvalidValueException("$class::findIdentity() must return an object implementing IdentityInterface.");
                } elseif (!$identity->validateAuthKey($authKey)) {
                    Yii::warning("Invalid auth key attempted for user '$id': $authKey", __METHOD__);
                } else {
                    return ['identity' => $identity, 'duration' => $duration];
                }
            }
        }
        $this->removeIdentityCookie();
        return null;
    }
     
    /**
     * Removes the identity cookie.
     * This method is used when [[enableAutoLogin]] is true.
     * @since 2.0.9
     */
    protected function removeIdentityCookie()
    {
        Yii::$app->getResponse()->getCookies()->remove(new Cookie($this->identityCookie));
    }

    /**
     * Switches to a new identity for the current user.
     *
     * When [[enableSession]] is true, this method may use session and/or cookie to store the user identity information,
     * according to the value of `$duration`. Please refer to [[login()]] for more details.
     *
     * This method is mainly called by [[login()]], [[logout()]] and [[loginByCookie()]]
     * when the current user needs to be associated with the corresponding identity information.
     *
     * @param IdentityInterface|null $identity the identity information to be associated with the current user.
     * If null, it means switching the current user to be a guest.
     * @param integer $duration number of seconds that the user can remain in logged-in status.
     * This parameter is used only when `$identity` is not null.
     */
    public function switchIdentity($identity, $duration = 0)
    {
    	
    	//var_dump($identity);
        $this->setIdentity($identity);

        if (!$this->enableSession) {
            return;
        }

        /* Ensure any existing identity cookies are removed. */
        if ($this->enableAutoLogin) {
            $this->removeIdentityCookie();
        }
         
        //var_dump($this->authTimeoutParam);
        $session = Yii::$app->getSession();
        if (!YII_ENV_TEST) {
            $session->regenerateID(true);
        }
        $session->remove($this->idParam);
        $session->remove($this->authTimeoutParam);
        
        if ($identity) {
            $session->set($this->idParam, $identity->getId());
            if ($this->authTimeout !== null) {
                $session->set($this->authTimeoutParam, time() + $this->authTimeout);
            }
            if ($this->absoluteAuthTimeout !== null) {
                $session->set($this->absoluteAuthTimeoutParam, time() + $this->absoluteAuthTimeout);
            }
            if ($duration > 0 && $this->enableAutoLogin) {
                $this->sendIdentityCookie($identity, $duration);
            }
        }
    }

    /**
     * Updates the authentication status using the information from session and cookie.
     *
     * This method will try to determine the user identity using the [[idParam]] session variable.
     *
     * If [[authTimeout]] is set, this method will refresh the timer.
     *
     * If the user identity cannot be determined by session, this method will try to [[loginByCookie()|login by cookie]]
     * if [[enableAutoLogin]] is true.
     */
    protected function renewAuthStatus()
    {
        $session = Yii::$app->getSession();
        $id = $session->getHasSessionId() || $session->getIsActive() ? $session->get($this->idParam) : null;

        if ($id === null) {
            $identity = null;
        } else {
            /* @var $class IdentityInterface */
            $class = $this->identityClass;
            $identity = $class::findIdentity($id);
        }

        $this->setIdentity($identity);

        if ($identity !== null && ($this->authTimeout !== null || $this->absoluteAuthTimeout !== null)) {
            $expire = $this->authTimeout !== null ? $session->get($this->authTimeoutParam) : null;
            $expireAbsolute = $this->absoluteAuthTimeout !== null ? $session->get($this->absoluteAuthTimeoutParam) : null;
            if ($expire !== null && $expire < time() || $expireAbsolute !== null && $expireAbsolute < time()) {
                $this->logout(false);
            } elseif ($this->authTimeout !== null) {
                $session->set($this->authTimeoutParam, time() + $this->authTimeout);
            }
        }

        if ($this->enableAutoLogin) {
            if ($this->getIsGuest()) {
                $this->loginByCookie();
            } elseif ($this->autoRenewCookie) {
                $this->renewIdentityCookie();
            }
        }
    }

    /**
     * Checks if the user can perform the operation as specified by the given permission.
     *
     * Note that you must configure "authManager" application component in order to use this method.
     * Otherwise it will always return false.
     *
     * @param string $permissionName the name of the permission (e.g. "edit post") that needs access check.
     * @param array $params name-value pairs that would be passed to the rules associated
     * with the roles and permissions assigned to the user.
     * @param boolean $allowCaching whether to allow caching the result of access check.
     * When this parameter is true (default), if the access check of an operation was performed
     * before, its result will be directly returned when calling this method to check the same
     * operation. If this parameter is false, this method will always call
     * [[\yii\rbac\CheckAccessInterface::checkAccess()]] to obtain the up-to-date access result. Note that this
     * caching is effective only within the same request and only works when `$params = []`.
     * @return boolean whether the user can perform the operation as specified by the given permission.
     */
    public function can($permissionName, $params = [], $allowCaching = true)
    {
    	$p = $permissionName; $access = false;
    	if(!is_array($p)){$p = array($permissionName);}
    	$type = 'OR';
    	if(strtoupper($p[0]) == 'AND'){
    		$type = 'AND';
    		unset($p[0]);
    	}
    	 
    	foreach ($p as $permissionName){
    		
	        if ($allowCaching && empty($params) && isset($this->_access[$permissionName])) {
	            $access = $this->_access[$permissionName];
	            if($type == 'OR' && $access){
	            	break;
	            }elseif($type == 'AND' && !$access) {
	            	return false;
	            	break;
	            }
	        }
	        if (($accessChecker = $this->getAccessChecker()) === null) {
	            $access = false; break;
	        }
	        $access = $accessChecker->checkAccess($this->getId(), $permissionName, $params);
	        if ($allowCaching && empty($params)) {
	            $this->_access[$permissionName] = $access;
	        }
	        if($access === true) return $access;
    	}
    	 
        return $access;
    }

    /**
     * Checks if the `Accept` header contains a content type that allows redirection to the login page.
     * The login page is assumed to serve `text/html` or `application/xhtml+xml` by default. You can change acceptable
     * content types by modifying [[acceptableRedirectTypes]] property.
     * @return boolean whether this request may be redirected to the login page.
     * @see acceptableRedirectTypes
     * @since 2.0.8
     */
    protected function checkRedirectAcceptable()
    {
        $acceptableTypes = Yii::$app->getRequest()->getAcceptableContentTypes();
        if (empty($acceptableTypes) || count($acceptableTypes) === 1 && array_keys($acceptableTypes)[0] === '*/*') {
            return true;
        }

        foreach ($acceptableTypes as $type => $params) {
            if (in_array($type, $this->acceptableRedirectTypes, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns auth manager associated with the user component.
     *
     * By default this is the `authManager` application component.
     * You may override this method to return a different auth manager instance if needed.
     * @return \yii\rbac\ManagerInterface
     * @since 2.0.6
     * @deprecated Deprecated since version 2.0.9, to be removed in 2.1. Use `getAccessChecker()` instead.
     */
    protected function getAuthManager()
    {
        return Yii::$app->getAuthManager();
    }

    /**
     * Returns the access checker used for checking access.
     * @return CheckAccessInterface
     * @since 2.0.9
     */
    protected function getAccessChecker()
    {
        return $this->accessChecker !== null ? $this->accessChecker : $this->getAuthManager();
    }
    
    /**
     * Load tour function
     */
    
    private $_model;
    
    public function getModel(){
        if($this->_model === null){
            $this->_model = Yii::createObject($this->identityClass);
        }
        
        return $this->_model;
    }
    
    private $_season;
    public function getSeason(){
        if($this->_season === null){
            $this->_season = Yii::createObject('izi\models\Season');
        }
        
        return $this->_season;
    }
    
    
    /**
     * Customize function
     */
    public function getItem($id, $params = []){
        return $this->getModel()->getItem($id, $params);
    }
    
    public function findCustomer($params){
        return $this->getModel()->findCustomer($params);
    }
    
    
    public function getSuggestCustomer($params){
        
        if(isset($params['customer']) && !empty($params['customer'])){
            
        }else{
            $params['customer'] = $this->getItem($params['customer_id']);
        }
        
        if(!isset($params['type_id']) && !empty($params['customer'])){
            $params['type_id'] = $params['customer']['type_id'];
        }
        
        if(!(isset($params['place']) && !empty($params['place']))){        
            $params['place'] = $this->getModel()->getPlaceIdByCustomer($params['customer']['id']);
        }
        
        if(!(isset($params['allow_me']) && $params['allow_me'] === true)){
            $params['not in'] = $params['customer']['id'];
        }
        
        return $this->findCustomer($params);
        
    }
    
    
    public function getCustomerCode($customer_id){
        $user = $this->getModel()->getItem($customer_id);
        return isset($user['code'])? $user['code'] : '';
    }
    
    public function cloneItem($from_id, $to_id =0){
        return $this->getModel()->cloneItem($from_id, $to_id);
    }
    
    
    public function getAutoCode($user_id, $user = []){
        if(!!empty($user)){
            $user = $this->getModel()->getItem($user_id);
        }
        
        $setting = isset(Yii::$app->cfg->app['settings']['users'][0]['code'])
        ? Yii::$app->cfg->app['settings']['users'][0]['code'] : [];
        
        
//         view(Yii::$app->cfg->app);
        
        $code_length = isset($setting['code_length']) ? $setting['code_length'] : 6;
        
        
        
        $code_regex = $code_regex_rs = isset($setting['code_regex']) ? $setting['code_regex'] : 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        
        $names = explode(' ', trim($user['name']));
        
        
        
        $firstLetter = unMark(substr($names[0], 0,1),'',false);
        $lastLetter = unMark(substr($names[count($names)-1], 0,1),'',false);
        
        if($lastLetter == ""){
            $lastLetter = randString(1, $names[0]);
        }
        
        if($firstLetter != "" && strpos($code_regex, $firstLetter) === false){
            $firstLetter = randString(1, $code_regex);
        }
        
        if($lastLetter != "" && strpos($code_regex, $lastLetter) === false){
            $lastLetter = randString(1, $code_regex);
            
            //if($lastLetter == ""){
//                 view($lastLetter);
//             }
        }
        
        $middleLength = $code_length - 2;
        
        $midLetter = '';
        
        if($middleLength > 0){
            for($i = 1; $i<count($names)-1 && $i<$middleLength+1; $i++){
                $midLetter .= substr($names[$i], 0,1);
            }
        }
        
        if(strlen($midLetter) < $middleLength){
            $midLetter .= randString($middleLength - strlen($midLetter), $code_regex);
        }
         
        
        $code = strtoupper(unMark($firstLetter . $midLetter . $lastLetter,'',false));
         
        
        $count = 0;
        while((new \yii\db\Query())->where(['and',['sid'=>__SID__,'code'=>$code],['not in', 'id', $user_id]])->from($this->getModel()->tableName())->count(1)>0){
            $code = strtoupper($firstLetter . randString($middleLength, $code_regex) . $lastLetter);
            if($count++ > 10){
                $code = strtoupper($firstLetter . randString($middleLength, $code_regex) . randString(1, $code_regex));
            }elseif($count > 100){
                $code = strtoupper( randString($code_length, $code_regex));
            }
            $code = unMark($code,'',false);
        }
        
        return $code;
        
    }
    
    public function showNameByLanguage($customer_info, $lang = __LANG__){
    	$name = '';
    	$short_name = isset($customer_info['short_name']) ? $customer_info['short_name'] : '';
    	switch($lang){
    		case 'vi-VN':
    			$name = showGenderName($customer_info['gender']). $customer_info['name'];
    			break;
    		default:
    			$name = showGenderName($customer_info['gender']). unMark($customer_info['fname'], ' ', false) . ' '. unMark($customer_info['lname'], ' ', false);
    			break;
    	}
    	
    	return $name . ($short_name != "" ? " ($short_name)" : '');
    }
    
    public function showSupplierNameByLanguage($customer_info, $lang = __LANG__){
         
        
        if(isset($customer_info['names'][$lang]) && $customer_info['names'][$lang] != ""){
            return $customer_info['names'][$lang];
        }
        $lang_code = Yii::$app->t->getCustomerLangcode($customer_info['id']);
        
        $name = Yii::$app->t->translate($lang_code, $lang);
        
        return $name == $lang_code ? Yii::$app->t->translate($lang_code, 'en_US', ['default'=>$customer_info['name']]) : $name;
    }
    
    public function showSupplierStreetByLanguage($customer_info, $lang = __LANG__){
                
        if(isset($customer_info['streets'][$lang]) && $customer_info['streets'][$lang] != ""){
            return $customer_info['streets'][$lang];
        }
        
        return isset($customer_info['street']) ? $customer_info['street'] : "";
        
        
    }
    
    
    public function getSugguestCustomer($params)
    {
         
         
        $params['is_active'] = 1;
        
        if(!(isset($params['rating']) && is_numeric($params['rating']) && $params['rating'] > 0)){
            $params['rating'] = null;
        }
        
        $supplier = $this->getModel()->findCustomer($params);
         
        
        if(!empty($supplier)) {
            
            switch ($role = (isset($params['role']) ? $params['role'] : null)) {
                
                case 'cheapest': // Tìm giá rẻ nhất
                    return $supplier[0];
                    break;
                
                default:
                    return $supplier[0];
                    break;
            }
            
            
        }
        
        if(isset($params['rating']) && is_numeric($params['rating'])){
            $params['rating']--;
            
            if($params['rating'] < 1){
                unset($params['rating']);
            }
            
            return $this->getSugguestCustomer($params);
        }
        
        
        if(isset($params['place'])){
            unset($params['place']);
            return $this->getSugguestCustomer($params);
        }
    }
    
    
    
    public function getSugguestCustomers($params)
    {
        $params['is_active'] = 1;
        
        if(!(isset($params['rating']) && is_numeric($params['rating']) && $params['rating'] > 0)){
            $params['rating'] = null;
        }
        
        $supplier = $this->getModel()->findCustomer($params);
        
        if(!empty($supplier)) {
            
            switch ($role = (isset($params['role']) ? $params['role'] : null)) {
                
                case 'cheapest': // Tìm giá rẻ nhất
                    return $supplier;
                    break;
                    
                default:
                    return $supplier;
                    break;
            }
            
            
        }
        
        if(isset($params['rating']) && is_numeric($params['rating'])){
            $params['rating']--;
            
            if($params['rating'] < 1){
                unset($params['rating']);
            }
            
            return $this->getSugguestCustomers($params);
        }
        
        
        if(isset($params['place'])){
            unset($params['place']);
            return $this->getSugguestCustomers($params);
        }
    }
    
}
