/*
 * Created on 31.05.2005
 *
 * TODO To change the template for this generated file go to
 * Window - Preferences - Java - Code Style - Code Templates
 */

/**
 * @author David
 *
 * TODO To change the template for this generated type comment go to
 * Window - Preferences - Java - Code Style - Code Templates
 */
//Class for camera management and camera transactions in P3D
package CampusMap;
import processing.core.*;

import java.lang.reflect.Method;
import java.lang.Class;
import java.util.Vector;

class Camera{
	//this one needs to be > 0.4 or problems with yaw/pitch
	static	final	float	birdViewHeightMultiplier = -0.5f;
	static	final	float	maxCameraHeight = 1200.0f;
	static	final	float	minInteractiveCameraHeight = 75.0f;
	static	final	float	minCameraHeight = 3.0f;

	static final	int		averageSuspectedFrameRate = 15;

	private CampusMap applet;			//reference to the applet

	private Vector actionQueue;

	//current camera state
	private FVector	m_vPos;				//current position
	private FVector	m_vCenter;			//current point-of-view / center
	private FVector	m_vUp;				//current up vector, describing the roll of the camera
	private FVector	m_vLeft;			//current left vector (crossproduct of up vector and look direction
	private FVector	m_vLook;			//current look direction (point-of-view - position)
	private float	m_fRoll;			//current roll value in rad

	//future state of the current camera animation
	private FVector	m_vPosFrom; // lastCameraPos to calculate bezier Curve
	private FVector	m_vPosTo;
	public FVector[] m_vControlPoints;
	private FVector	m_vCenterTo;
	private float	m_fRollTo;
	private float	m_fRotateTo;
	private float	m_fRotateHeightTo;

	//direction of the current camera animation (the way the camera has to go to the future state)
	private FVector	m_vPosDir;
	private FVector	m_vCenterDir;
	private float	m_fRollDir;
	private FVector	m_vRotateDir;
	private float	m_fRotateHeightDir;

	//camera frustum values
	private float	m_fAspectRatio;
	private float	m_fNearClip;
	private float	m_fFarClip;
	private float	m_fFov;
	private float	m_fDistanceEyeMousePlane;
	private float	m_fMousePlaneMultiplier;


	//time (ms) when the current animation started
	private int		m_iMoveStartTime;
	private int		m_iLookStartTime;
	private int		m_iRollStartTime;
	private int		m_iRotateStartTime;

	//length (ms) of the current animation
	private int		m_iMoveDuration;
	private int		m_iLookDuration;
	private int		m_iRollDuration;
	private int		m_iRotateDuration;

	//if the current animation is accelerated or linear/ on a bezierline
	private int		m_iMoveAccel;
	public	boolean	m_bMoveBezier;
	private int		m_iLookAccel;
	private int		m_iRollAccel;
	private int		m_iRotateAccel;

	//set to true if anything signficant has happend and the camera state has to be recalculated
	private boolean	m_bCameraHasChanged;

	//framerate calculation and drawing values
	private int		m_iFrameCount;
	private boolean	m_bDrawFramerate;
	public boolean	m_bFrameRateDrawingOn = false;
	private int		m_iLastSecond;
	//BitmapFont 		m_framerate;
	private int		m_iFramerate;
	private float	m_fFramerateMultiplier;

	private Slider	m_heightSlider = null;
        private FVector mousePoint;                        // point on sreen where mouse pointer

	private Building	m_iLastCircleViewBuilding;


	//constructor
	public Camera(CampusMap applet) {
		this.applet = applet;
		applet.env.objectInitDisplay.setText("Camera");

		actionQueue = new Vector();

		m_vPos		= new FVector();
		m_vCenter	= new FVector();
		m_vUp		= new FVector();
		m_vLeft		= new FVector();
		m_vLook		= new FVector();

		//start camera values (normal view)
		m_vCenter.z	= 1;
		m_vUp.y		= 1;
		m_vLook.z	= -1;
		m_fRoll			= 0.0f;

		m_vPosTo			= new FVector();
		m_vCenterTo			= new FVector();
		m_fRollTo			= 0.0f;
		m_fRotateTo			= 0.0f;
		m_fRotateHeightTo	= 0.0f;

		m_vPosDir			= new FVector();
		m_vCenterDir		= new FVector();
		m_fRollDir			= 0.0f;
		m_vRotateDir		= new FVector();
		m_fRotateHeightDir	= 0.0f;

		//standard camera frustum values
		m_fAspectRatio	= (float)applet.width / (float)applet.height;
		m_fNearClip		= (float)(((applet.height/2.0) / Math.tan(Math.PI*60.0/360.0)) / 10);
		m_fFarClip		= (float)(((applet.height/2.0) ) * 10);
		m_fFov			= (float)(Math.PI / 3.0f);
		m_fDistanceEyeMousePlane	= (float)((applet.height / 2) / Math.tan(m_fFov / 2));
		m_fMousePlaneMultiplier	= m_fNearClip / m_fDistanceEyeMousePlane;

		m_iMoveStartTime	= 0;
		m_iLookStartTime	= 0;
		m_iRollStartTime	= 0;
		m_iRotateStartTime	= 0;

		m_iMoveDuration		= 0;
		m_iLookDuration		= 0;
		m_iRollDuration		= 0;
		m_iRotateDuration	= 0;

		m_iMoveAccel		= 0;
		m_iLookAccel		= 0;
		m_iRollAccel		= 0;
		m_iRotateAccel		= 0;
		m_bMoveBezier		= false;

		m_bCameraHasChanged	= true;

		m_iFramerate		= 0;
		m_fFramerateMultiplier = 0.0f;
		m_iFrameCount		= 0;
		m_bDrawFramerate	= true;
		m_iLastSecond		= 0;

		m_iLastCircleViewBuilding = null;
	}

	//queues one of the camera's Methods
	public void queueAction(String methodName, int p_waitTime, Object[] p_parameters) {
		Class[] parameterClasses = new Class[p_parameters.length];
		for (int i = 0; i < p_parameters.length; i++) {
			parameterClasses[i] = p_parameters[i].getClass();
		}
		try {
			Method cameraMethod = this.getClass().getMethod(methodName, parameterClasses);
			actionQueue.add(new CameraAction(this, cameraMethod, p_waitTime, p_parameters));
		}catch (Exception e) {
			System.out.println("Camera-queueAction(): " + e);
		}
	}

	public void checkActionQueue() {
		if ( !(actionQueue.isEmpty()) && ((CameraAction)actionQueue.firstElement()).check(applet.millis()) ) {
			actionQueue.remove(0);
		}
	}

	public void setInstantCircleView(Boolean onOff) {
		applet.controls.setTempDisable(onOff.booleanValue());
	}

	public void registerHeightSlider(Slider heightSlider) {
		m_heightSlider = heightSlider;
	}

	public void adjustHeightSlider() {
		if (m_heightSlider!=null)
			m_heightSlider.setValue((maxCameraHeight - m_vPos.z)/ (maxCameraHeight - minInteractiveCameraHeight));
	}

	//teleports the camera position to the given coordinates
	public void moveToNow(Float x, Float y, Float z) {
		m_vPos.x = x.floatValue();
		m_vPos.y = y.floatValue();
		m_vPos.z = z.floatValue();
		m_iMoveStartTime = 0;
		m_bCameraHasChanged = true;
		adjustHeightSlider();
	}

	//teleports the camera position to the given coordinates
	public void moveToNow(FVector to) {
		m_vPos = to;
		m_iMoveStartTime = 0;
		m_bCameraHasChanged = true;
		adjustHeightSlider();
	}

	//moves the camera position to the given coordinates within the specified time (interlaced/animated)
	public void moveToInter(FVector to, Integer duration, Integer acceleration) {
		m_vPosTo			= to;
		m_vPosDir			= FVector.subtract(m_vPosTo, m_vPos);
		m_iMoveDuration		= duration.intValue();
		m_iMoveStartTime	= applet.millis();
		m_iMoveAccel		= acceleration.intValue();
	}

	public void moveToInter(FVector from, FVector to, FVector[] p_controlPoints, Integer duration) {
		m_vPosTo			= to;
		m_vPosFrom			= from;
		m_vControlPoints	= p_controlPoints;
		m_vPosDir			= FVector.subtract(m_vPosTo, m_vPos);
		m_iMoveDuration		= duration.intValue();
		m_iMoveStartTime	= applet.millis();
		m_bMoveBezier		= true;
	}

	//moves the camera position to the given coordinates within the specified time (interlaced/animated)
	public void moveToInter(Float x, Float y, Float z, Integer duration, Integer acceleration) {
		m_vPosTo.x		= x.floatValue();
		m_vPosTo.y		= y.floatValue();
		m_vPosTo.z		= z.floatValue();
		m_vPosDir			= FVector.subtract(m_vPosTo, m_vPos);
		m_iMoveDuration		= duration.intValue();
		m_iMoveStartTime	= applet.millis();
		m_iMoveAccel		= acceleration.intValue();
	}

	public void step(Float directionInDegree, Float distance) {
		FVector offsetCam = new FVector(m_vLook);
		offsetCam.setZ(0);
		offsetCam.normalizeMe();
		offsetCam.rotateMeZ(PApplet.radians(directionInDegree.floatValue()));
		offsetCam.multiplyMe(distance.floatValue());
		FVector newPos = FVector.add(offsetCam, m_vPos);
		if (applet.groundPlane.check(newPos).isZero() || applet.groundPlane.check(newPos).magnitudeSqr() < applet.groundPlane.check(m_vPos).magnitudeSqr()) {
			moveToNow(newPos);
			lookAtNow(FVector.add(offsetCam, m_vCenter));
		}
	}

	// moves the camera forward on the current offset-plane
	public void stepForward(){
		step(new Float(0.0f), new Float(40.0f/getFramerateMultiplier()));
	}

	// moves the camera forward on the current offset-plane
	public void stepBackward(){
		step(new Float(180.0f), new Float(40.0f/getFramerateMultiplier()));
	}

	// moves the camera forward on the current offset-plane
	public void stepLeft(){
		step(new Float(270.0f), new Float(80.0f/getFramerateMultiplier()));
	}

	// moves the camera forward on the current offset-plane
	public void stepRight(){
		step(new Float(90.0f), new Float(80.0f/getFramerateMultiplier()));
	}

	public void moveCameraVertically(int value) {
		FVector camPos = getPosInAction();
		if (camPos.isZero()) camPos = getPos();
		camPos.addMe(new FVector(0,0,(value * -100)));
		if (camPos.getZ() > maxCameraHeight) camPos.setZ(maxCameraHeight);
		else if (camPos.getZ() < minInteractiveCameraHeight) camPos.setZ(minInteractiveCameraHeight);
		FVector storeLook = getScreenCenterGroundPlaneIntersection(false);
		if (!(storeLook.isZero())) {
			lookAtNow(storeLook);
		}
		moveToInter(camPos, new Integer (200), new Integer(1) );

	}

	//teleports the camera pov to the given coordinates
	public void lookAtNow(Float x, Float y, Float z) {
		m_vCenter.x		= x.floatValue();
		m_vCenter.y		= y.floatValue();
		m_vCenter.z		= z.floatValue();
		m_iLookStartTime	= 0;
		m_bCameraHasChanged	= true;
	}

	//teleports the camera pov to the given coordinates
	public void lookAtNow(FVector at) {
		m_vCenter			= at;
		m_iLookStartTime	= 0;
		m_bCameraHasChanged	= true;
	}

	//moves the camera pov to the given coordinates within the specified time (interlaced/animated)
	public void lookAtInter(FVector to, Integer duration, Integer acceleration) {
		limitPovDistance();
		m_vCenterTo			= to;
		m_vCenterDir		= FVector.subtract(m_vCenterTo, m_vCenter);
		m_iLookDuration		= duration.intValue();
		m_iLookStartTime	= applet.millis();
		m_iLookAccel		= acceleration.intValue();
	}

	//moves the camera pov to the given coordinates within the specified time (interlaced/animated)
	public void lookAtInter(Float x, Float y, Float z, Integer duration, Integer acceleration) {
		limitPovDistance();
		m_vCenterTo.x	= x.floatValue();
		m_vCenterTo.y	= y.floatValue();
		m_vCenterTo.z	= z.floatValue();
		m_vCenterDir		= FVector.subtract(m_vCenterTo, m_vCenter);
		m_iLookDuration		= duration.intValue();
		m_iLookStartTime	= applet.millis();
		m_iLookAccel		= acceleration.intValue();
	}

	//sets the camera roll
	public void rollToNow(Float radiens) {
		m_fRoll 			= radiens.floatValue();
		m_iRollStartTime 	= 0;
		m_bCameraHasChanged = true;
	}

	//sets the camera roll within the specified time (interlaced/animated)
	public void rollToInter(Float to, Integer duration, Integer acceleration) {
		m_fRollTo			= to.floatValue();
		m_fRollDir			= m_fRollTo - m_fRoll;
		m_iRollDuration		= duration.intValue();
		m_iRollStartTime	= applet.millis();
		m_iRollAccel		= acceleration.intValue();
		m_bCameraHasChanged = true;
	}

	//rotates the camera position around the current pov
	public void rotateToNow(Float radiens, Float height) {
		//m_iRotateStartTime 	= 0;
		m_bCameraHasChanged = true;
		FVector povToPos = FVector.subtract(getPos(), getCenter());
		povToPos.rotateMeZ(radiens.floatValue());
		povToPos.addMe(getCenter());
		if (height.floatValue() != 0.0f)
			povToPos.setZ(height.floatValue());
		moveToNow(povToPos);
		//System.out.println("getPos().getZ(): " + getPos().getZ() + "   m_fRotateHeightTo: " + m_fRotateHeightTo + "   m_fRotateHeightDir: " + m_fRotateHeightDir);
	}

	//rotates the camera position around the current pov within the specified time (interlaced/animated)
	public void rotateToInter(Float to, Float height, Integer duration, Integer acceleration) {
		m_fRotateTo			= to.floatValue();
		m_vRotateDir		= FVector.subtract(getCenter(), getPos());
		//System.out.println("m_fRotateTo: " + m_fRotateTo + "  currentRot" + applet.atan2(m_vRotateDir.y, m_vRotateDir.x));
		m_iRotateDuration	= duration.intValue();
		m_iRotateStartTime	= applet.millis();
		m_iRotateAccel		= acceleration.intValue();
		m_bCameraHasChanged = true;
		if (height.floatValue() != 0.0f) {
			m_fRotateHeightTo = height.floatValue();
			m_fRotateHeightDir = m_fRotateHeightTo - getPos().getZ();
		}
		else m_fRotateHeightTo = 0.0f;
		//System.out.println("getPos().getZ(): " + getPos().getZ() + "   m_fRotateHeightTo: " + m_fRotateHeightTo + "   m_fRotateHeightDir: " + m_fRotateHeightDir);
	}

	public void yawLeft(){
		yaw(-0.04f/getFramerateMultiplier());
	}

	public void yawRight(){
		yaw(0.04f/getFramerateMultiplier());
	}

	public void pitchUp(){
		pitch(-0.04f/getFramerateMultiplier());
	}

	public void pitchDown(){
		pitch(0.04f/getFramerateMultiplier());
	}

	//changes the yaw (rotation around the z-axis)
	public void yaw(float rad) {
		if (rad != 0.0f) {
			rad = (rad>0.0f) ? Math.min(rad, 0.4f) : Math.max(rad, -0.4f); //limit yaw
			FVector tempRotated = FVector.rotateAxis(m_vLook, -rad, m_vUp);
			if ( (tempRotated.x*tempRotated.x + tempRotated.y*tempRotated.y) > 0.2f || m_fRoll == 0.0f) {
				m_vLook.set(tempRotated);
				m_vCenter.set(FVector.add(m_vPos, FVector.multiply(m_vLook, m_vCenter.magnitude())));
				updateUp();
				m_bCameraHasChanged = true;
			}
		}
	}

	//changes the pitch (rotation around the x-axis)
	public void pitch(float rad) {
		if (rad != 0.0f) {
			rad = (rad>0.0f) ? Math.min(rad, 0.4f) : Math.max(rad, -0.4f); //limit pitch
			FVector tempRotated = FVector.rotateAxis(m_vLook, -rad, m_vLeft);
			float tempRotatedXYLength	= tempRotated.x*tempRotated.x + tempRotated.y*tempRotated.y;
			float lookXYLength			= m_vLook.x*m_vLook.x + m_vLook.y*m_vLook.y;
			boolean keptSignes =	((tempRotated.x > 0 && m_vLook.x > 0) || (tempRotated.x <= 0 && m_vLook.x <= 0))
							&&		((tempRotated.y > 0 && m_vLook.y > 0) || (tempRotated.y <= 0 && m_vLook.y <= 0));
			if ( (tempRotatedXYLength > 0.2f && tempRotated.z < 0.0f) || (tempRotatedXYLength > 0.6f && tempRotated.z > 0.0f) || (tempRotatedXYLength > lookXYLength && keptSignes)) {
				m_vLook.set(tempRotated);
				m_vCenter.set(FVector.add(m_vPos, FVector.multiply(m_vLook, m_vCenter.magnitude())));
				updateUp();
				m_bCameraHasChanged = true;
			}
			//System.out.println("keptSignes" + keptSignes + "  tempRotatedXYLength "+ tempRotatedXYLength + "  lookXYLength " + lookXYLength);
		}
	}

	//recalculates the camera's up vector, so that it's always orthogonal to the look direction+
	private void updateUp() {
		// Describe the new vector between the camera and the target
		FVector helper = new FVector();

		if ( m_vLook.z	>	(Math.sqrt ( m_vLook.x * m_vLook.x	+	m_vLook.y * m_vLook.y ) ) ) {
			m_vUp.x = 0;
			m_vUp.y = 1;
			m_vUp.z = 0;
			helper = new FVector(m_vLook);
			helper.y = 0;
		}
		else {
			m_vUp.x = 0;
			m_vUp.y = 0;
			m_vUp.z = 1;
			helper = new FVector(m_vLook);
			helper.z = 0;
		}
		helper.normalizeMe();
		helper = (FVector.crossProduct(helper, m_vUp)).normalize();
		m_vUp	= (FVector.crossProduct(m_vLook, helper)).normalize();


		// Calculate the roll if there is one
		if (m_fRoll != 0.0) {
			m_vUp = FVector.multiply(m_vUp, (float)(Math.cos(m_fRoll)));
			m_vUp = FVector.add(m_vUp, FVector.multiply(helper, (float)(Math.sin(m_fRoll))));
		}
	}

	//updates the three vectors describing the camera look direction
	private void updateValues() {
		m_vLook = (FVector.subtract(m_vCenter, m_vPos)).normalize();
		updateUp();
		m_vLeft = (FVector.crossProduct(m_vUp, (FVector.subtract(m_vCenter, m_vPos)).normalize())).normalize();
	}

	//calculates the current position in the current animation
	private void updatePos() {
		int timePassed = applet.millis() - m_iMoveStartTime;
		if ( timePassed > m_iMoveDuration	||	m_vPosTo.equals(m_vPos) ) {
			m_iMoveStartTime = 0;
			m_vPosTo = new FVector();
			if (m_iMoveAccel==0)
				m_vPos = m_vPosTo;
			m_bMoveBezier=false;
		}
		else {
			float timePassedOfTotal = ((float)timePassed) / ((float)m_iMoveDuration);
			if(!m_bMoveBezier){
				timePassedOfTotal = FMath.calcAcceleration(timePassedOfTotal, m_iMoveAccel);
				m_vPos = FVector.subtract(m_vPosTo, FVector.multiply(m_vPosDir, 1 - timePassedOfTotal));
			} else {
				float[] bezierPos = FMath.bezier(m_vPosFrom, m_vControlPoints[0], m_vControlPoints[1], m_vPosTo, timePassedOfTotal);
				m_vPos = new FVector(bezierPos[0], bezierPos[1],
						(float)m_vPosTo.getZ()-(m_vPosDir.getZ()*(1-timePassedOfTotal)));
				//m_vPos.printMe("BezierVector: ");
			}
			m_bCameraHasChanged = true;
			Overview.setLookPoint(m_vPos);
		}
		adjustHeightSlider();
	}

	//calculates the current pov/center in the current animation
	private void updateCenter() {
		int timePassed = applet.millis() - m_iLookStartTime;
		if ( timePassed > m_iLookDuration	||	m_vCenterTo.equals(m_vCenter) ) {
			m_iLookStartTime = 0;
			if (m_iLookAccel==0)
				m_vCenter = m_vCenterTo;
		}
		else {
			float timePassedOfTotal = ((float)timePassed) / ((float)m_iLookDuration);
			timePassedOfTotal = FMath.calcAcceleration(timePassedOfTotal, m_iLookAccel);
			m_vCenter = FVector.subtract(m_vCenterTo, FVector.multiply(m_vCenterDir, 1- timePassedOfTotal));
			//m_vCenter.printMe("m_vCenter");
			m_bCameraHasChanged = true;
		}
	}

	//calculates the current roll in the current animation
	private void updateRoll() {
		int timePassed = applet.millis() - m_iRollStartTime;
		if ( timePassed > m_iRollDuration	||	m_fRollTo == m_fRoll ) {
			m_iRollStartTime = 0;
			if (m_iRollAccel==0)
				m_fRoll = m_fRollTo;
		}
		else {
			float timePassedOfTotal = ((float)timePassed) / ((float)m_iRollDuration);
			timePassedOfTotal = FMath.calcAcceleration(timePassedOfTotal, m_iRollAccel);
			m_fRoll = m_fRollTo - (m_fRollDir * (1- timePassedOfTotal));
			m_bCameraHasChanged = true;
		}
	}

	//calculates the current rotation around the pov in the current animation
	private void updateRotation() {
		int timePassed = applet.millis() - m_iRotateStartTime;
		if ( timePassed > m_iRotateDuration) {
			m_iRollStartTime = 0;
			if (m_iRotateAccel==0) {
				//moveToNow(getCenter().subtract(m_vRotateDir));
				//rotateToNow(new Float(m_fRotateTo), new Float(m_fRotateHeightTo));
			}
			m_fRotateHeightTo = 0.0f;
		}
		else {
			float timePassedOfTotal = ((float)timePassed) / ((float)m_iRotateDuration);
			timePassedOfTotal = FMath.calcAcceleration(timePassedOfTotal, m_iRotateAccel);

			moveToNow(FVector.subtract(getCenter(), m_vRotateDir));
			float currentRotation = ( m_fRotateTo * timePassedOfTotal);
			float currentHeight = 0.0f;
			if (m_fRotateHeightTo != 0.0)
				currentHeight = m_fRotateHeightTo - (m_fRotateHeightDir * (1-timePassedOfTotal));
			//System.out.println("currentHeight: " + currentHeight + "   m_fRotateHeightTo: " + m_fRotateHeightTo + "   real Height: " + m_vPos.getZ());
			//System.out.println("rot " + currentRotation);
			//System.out.println("rot " + timePassedOfTotal);
			//System.out.println("rot " + m_fRotateTo);
			rotateToNow(new Float(currentRotation), new Float(currentHeight));

			m_bCameraHasChanged = true;
		}
	}

	//changes the current height of the camera over the ground
	public void changeHeight(Float height, Boolean firstTime) {
		if (firstTime.booleanValue()) {
			//System.out.println("firstTime");
			FVector storeLook = getScreenCenterGroundPlaneIntersection(false);
			if (!(storeLook.isZero())) {
				//System.out.println("drawn");
				lookAtNow(storeLook);
			}
		}
		//System.out.println("new height: " + height);
		moveToNow(new Float(getPos().getX()), new Float(getPos().getY()), new Float(maxCameraHeight - height.floatValue() * (maxCameraHeight - minInteractiveCameraHeight)) );
	}

	//sets the camera over the target with the new height or the height of the slider, if height set to 0.0f
	public FVector birdView(FVector newTarget, float degree, int duration, float height) {
		FVector lookDirection = FVector.subtract(newTarget, getPos());
		lookDirection.setZ(0.0f);
		lookDirection.normalizeMe();

		if (height == 0.0f) {
			System.out.println("height: " + height + "   degree: " + degree);
			height = maxCameraHeight - applet.objectManager.guiObjects.getValue(0) * (maxCameraHeight - minInteractiveCameraHeight);
			degree *= (height / maxCameraHeight);
			System.out.println("height: " + height + "   degree: " + degree);
			lookDirection.multiplyMe( (-1 * height/applet.tan(PApplet.radians(degree))) * (height / maxCameraHeight));
			FVector addToTarget = FVector.multiply(FVector.subtract(newTarget, m_vPos).normalize(), height/2);
			lookAtInter(FVector.add(newTarget, addToTarget), new Integer(duration), new Integer(1));
		}
		else {
			lookDirection.multiplyMe(-1 * height/applet.tan(PApplet.radians(degree)));
			lookAtInter(newTarget, new Integer(duration), new Integer(1));
		}

		lookDirection.setZ(height);
		//System.out.println("birdView " + lookDirection.getX() + " " + lookDirection.getY() + " " + lookDirection.getZ());
		lookDirection.addMe(newTarget);
		moveToInter(lookDirection, new Integer(duration), new Integer(1));
		return lookDirection;
	}

	public void flyToRoom(Building targetBuilding, FVector roomPosition, boolean showRoom) {
//		Building targetBuilding = applet.objectManager.getBuildingByNumber(buildingNo);
		if (targetBuilding.equals(getLastCircleViewBuilding())) { //do not change building
			FVector vecToPos = FVector.subtract(getPos(), targetBuilding.getCenterPosition());
			targetBuilding.showPositionInBuilding(roomPosition);
//			applet.objectManager.makeBuildingsInvisible(targetBuilding);
			FVector flatCenterPoint = targetBuilding.getCenterPosition().cloneMe();
			flatCenterPoint.setZ(0);
			FVector vecToRoomPos = FVector.subtract(roomPosition, flatCenterPoint);
			float newHeight = calculateRoomCameraHeight(roomPosition, targetBuilding.getCenterPosition(), targetBuilding.flyAroundRadius);
			float flyIntoRotation = applet.atan2(vecToPos.getY(), vecToPos.getX());
			float toRoomRotation = applet.atan2(vecToRoomPos.getY(), vecToRoomPos.getX());
			float requiredRotation = toRoomRotation-flyIntoRotation;
			if (requiredRotation<0) requiredRotation += 2*PApplet.PI;
			rotateToInter(new Float(requiredRotation), new Float(newHeight), new Integer(2000), new Integer(1));
		}
		else {
			flyToPosInBuilding(roomPosition, targetBuilding.getCenterPosition(), targetBuilding.flyAroundRadius, 3500, 2000, targetBuilding, showRoom);
		}
		setLastCircleViewBuilding(targetBuilding);
	}

	public void flyToPosInBuilding(FVector roomPosition, FVector centerPoint, float radius, int durationFlyInto, int durationCircleAround, Building targetBuilding, boolean showRoom) {
		//applet.spheres.add(roomPosition);
		setInstantCircleView(Boolean.valueOf(false));
		FVector vecToPos = flyIntoCircleView(centerPoint, radius, 0.0f, durationFlyInto);
		FVector flatCenterPoint = centerPoint.cloneMe();
		flatCenterPoint.setZ(0);
		FVector vecToRoomPos = FVector.subtract(roomPosition, flatCenterPoint);
		//roomPosition.printMe("roomPosition");
		//flatCenterPoint.printMe("flatCenterPoint");
		//vecToRoomPos.printMe("vecToRoomPos");


		//rotation
		float flyIntoRotation = applet.atan2(vecToPos.getY(), vecToPos.getX());
		float toRoomRotation = applet.atan2(vecToRoomPos.getY(), vecToRoomPos.getX());
		float requiredRotation = toRoomRotation-flyIntoRotation;
		if (requiredRotation<0) requiredRotation += 2*PApplet.PI;
		//System.out.println("requiredRotation = toRoomRotation-flyIntoRotation: " + requiredRotation + " " + toRoomRotation + " " + flyIntoRotation);

		float newHeight = calculateRoomCameraHeight(roomPosition, centerPoint, radius);

		//queue action
		//System.out.println("flyIntoRotation " + flyIntoRotation + "  toRoomRotation " + toRoomRotation + "  requiredRotation " + requiredRotation);
		//provisorisch mit room height:
		//Object[] actionObjects = {new Float(requiredRotation), new Float(vecToRoomPos.getZ()), new Integer(durationCircleAround), new Boolean(true)};
		Object[] actionObjects = {new Float(requiredRotation), new Float(newHeight), new Integer(durationCircleAround), new Integer(3)};
		queueAction("rotateToInter", durationFlyInto-100, actionObjects);
		Object[] actionObjects2 = {new FVector(roomPosition), targetBuilding, Boolean.valueOf(showRoom)};
		queueAction("showRoomInBuilding", 0, actionObjects2);
	}

	private float calculateRoomCameraHeight(FVector roomPosition, FVector centerPoint, float radius) {
		FVector flatCenterPoint = centerPoint.cloneMe();
		flatCenterPoint.setZ(0);
		FVector vecToRoomPos = FVector.subtract(roomPosition, flatCenterPoint);
		float lengthToRoom = (float)(Math.sqrt ( vecToRoomPos.x * vecToRoomPos.x	+	vecToRoomPos.y * vecToRoomPos.y ) );
		float newHeight = (vecToRoomPos.z-centerPoint.z) * radius / lengthToRoom;
		//System.out.println("newHeight " + newHeight + "  vecToRoomPos.z " + vecToRoomPos.z + "  radius " + radius + "  lengthToRoom " + lengthToRoom);
		if (newHeight < 6.0f) newHeight = 6.0f;
		return newHeight;
	}

	public FVector flyIntoCircleView(FVector centerPoint, float radius, float degree, int duration) {
		FVector currentPos = getPos();
		FVector dirFromCenterToPos = FVector.subtract(centerPoint, currentPos).normalize();
		FVector dirFromCenterToSide = FVector.crossProduct(dirFromCenterToPos, new FVector(0,0,1));
		FVector newCameraPos = FVector.add(centerPoint, FVector.multiply(dirFromCenterToSide, radius));
		//centerPoint.printMe();
		//dirFromCenterToSide.printMe();
		float addHeight = applet.tan(PApplet.radians(degree)) * radius;
		//newCameraPos.printMe();
		newCameraPos.setZ(newCameraPos.getZ() + addHeight);

		//m_vCenter.printMe("m_vCenter zu anfang");
		moveToInter(newCameraPos, new Integer(duration), new Integer(2));
		//newCameraPos.printMe("newCameraPos");
		lookAtInter(centerPoint, new Integer(duration), new Integer(0));
		//centerPoint.printMe("centerPoint");

		//applet.spheres.add(newCameraPos);
		return FVector.subtract(newCameraPos, centerPoint);
	}

	public void showRoomInBuilding(FVector roomPosition, Building targetBuilding, Boolean showRoom) {
		if (showRoom.booleanValue())
			targetBuilding.showPositionInBuilding(roomPosition);
		else {
			Object[] actionObjects = {Boolean.valueOf(!showRoom.booleanValue())};
			applet.theCamera.queueAction("setInstantCircleView", 2000, actionObjects);
		}
			setInstantCircleView(Boolean.valueOf(!showRoom.booleanValue()));
		targetBuilding.setForceSelectedModel(true);
		applet.objectManager.makeBuildingsInvisible(targetBuilding);
	}

	public void setLastCircleViewBuilding(Building building) {
		//System.out.println("setLastCircleViewBuilding oldbuildingno: " + m_iLastCircleViewBuilding + "   newbuildingno: " + buildingNo);
		m_iLastCircleViewBuilding = building;
	}

	public Building getLastCircleViewBuilding() {
		return m_iLastCircleViewBuilding;
	}

	public void guaranteeMinInteractiveCameraHeight() {
		if (getPos().getZ() < minInteractiveCameraHeight) {
			FVector newPos = new FVector(getPos());
			newPos.setZ(minInteractiveCameraHeight);
			moveToInter(newPos, new Integer(1000), new Integer(2));
		}
	}

	//for frustum changes... TODO
	public void setPerspective() {
		//perspective(fov, aspect, cameraZ/10.0, cameraZ*10.0);
	}

	//applies all the animations and frustum values to the applet's rendering system
	public boolean apply(){
		if (m_iMoveStartTime	> 0)	updatePos();
		if (m_iLookStartTime	> 0)	updateCenter();
		if (m_iRollStartTime	> 0)	updateRoll();
		if (m_iRotateStartTime	> 0)	updateRotation();
		if (m_bCameraHasChanged)		updateValues();
		if (applet.controls.getEnabled()) {
			if (applet.controls.upButtonPressed)
				stepForward();
			if (applet.controls.downButtonPressed)
				stepBackward();
			if (applet.controls.leftButtonPressed)
				stepLeft();
			if (applet.controls.rightButtonPressed)
				stepRight();
		}
		applet.perspective(m_fFov, m_fAspectRatio, m_fNearClip, m_fFarClip);
		applet.camera(m_vPos.x, m_vPos.y, m_vPos.z,
				 m_vCenter.x, m_vCenter.y, m_vCenter.z,
				 m_vUp.x, m_vUp.y, m_vUp.z);
		checkActionQueue();
		boolean returnChanges = m_bCameraHasChanged;
		m_bCameraHasChanged = false;
		return returnChanges;
	}

	//calculates the ray, that goes form the camera position through the mousepoint into the world
	public FVector getMousePointRay() {
		mousePoint = FVector.multiply(getLook(), m_fDistanceEyeMousePlane);
		mousePoint = FVector.add(mousePoint, FVector.multiply(getLeft(),  (float)((applet.mouseX - applet.width / 2) * -1) ) );
		mousePoint = FVector.add(mousePoint, FVector.multiply(getUp(), (float)(applet.mouseY - applet.height / 2) ) );
		return mousePoint;
	}

	//calculates the position on the ground, that the mouse points on
	public FVector getMouseGroundPlaneIntersection(boolean checkWithGroundPlane) {
		FVector mousePoint = getMousePointRay();

		FVector intersection = new FVector();
		if (mousePoint.z != 0) {
			float multiplier = -getPos().z / mousePoint.z;
			if (multiplier > 0)
				intersection = FVector.add(getPos(), FVector.multiply(mousePoint, multiplier));
		}
        // World-Borderline limitation
		if (checkWithGroundPlane) intersection = applet.groundPlane.limit(intersection);
		return intersection;
	}

	//calculates the position on the ground, that the screen center points on
	public FVector getScreenCenterGroundPlaneIntersection(boolean limit) {
		FVector centerPoint = getLook();

		FVector intersection = new FVector();
		if (centerPoint.z != 0) {
			float multiplier = -getPos().z / centerPoint.z;
			if (multiplier > 0)
				intersection = FVector.add(getPos(), FVector.multiply(centerPoint, multiplier));
		}
        // World-Borderline limitation
		if (limit)
			intersection = applet.groundPlane.limit(intersection);
		return intersection;
	}

	public void limitPovDistance() {
		FVector currentPov = getScreenCenterGroundPlaneIntersection(false);
		if (currentPov.isZero())
			m_vCenter = FVector.add(m_vPos, FVector.multiply(FVector.subtract(m_vCenter, m_vPos).normalize(), m_vPos.getZ()));
		else m_vCenter = currentPov;
	}

	//deprecated... :)
	public FVector get3DFrom2D(int x, int y) { //  - deprecated
		//float heightMultiplier = tan(getVerticalFrustumAngle() / 2.0) * getNearClip() / (height /2.0);
		//float widthMultiplier	= heightMultiplier * getAspectRatio();

		FVector result = m_vPos;

		//result.addToMe(getLook().multiply(getNearClip()));
		//result.addToMe(getLeft().multiply( (x - ((float)width)	/ 2.0) * widthMultiplier));
		//result.addToMe(getUp().multiply( (y - ((float)height)	/ 2.0) * heightMultiplier));

		return result;
	}

	//get methods
	public FVector getLook() {
		return m_vLook;
	}

	public FVector getUp() {
		return m_vUp;
	}

	public FVector getLeft() {
		return m_vLeft;
	}

	public FVector getCenter() {
		return m_vCenter;
	}

	public FVector getPos() {
		return m_vPos;
	}

	public FVector getPosInAction() {
		if (m_iMoveStartTime != 0)
			return m_vPosTo.cloneMe();
		else return new FVector();
	}

	public FVector getCenterInAction() {
		if (m_iLookStartTime != 0)
			return m_vCenterTo.cloneMe();
		else return new FVector();
	}

	public FVector getOriginPos() {
		return m_vPosFrom.cloneMe();
	}

	public FVector getTargetPos() {
		return m_vPosTo.cloneMe();
	}

	//set methods
	public void setVerticalFov(float angle) {
		m_fFov = angle;
		m_fDistanceEyeMousePlane	= (float)((applet.height / 2) / Math.tan(m_fFov / 2));
		m_fMousePlaneMultiplier		= m_fNearClip / m_fDistanceEyeMousePlane;
	}

	public void setNearClip(float nearClip) {
		m_fNearClip = nearClip;
		m_fDistanceEyeMousePlane	= (float)((applet.height / 2) / Math.tan(m_fFov / 2));
		m_fMousePlaneMultiplier	= m_fNearClip / m_fDistanceEyeMousePlane;
	}

	public void setFarClip(float farClip) {
		m_fFarClip = farClip;
	}

	//draws the framerate to the screen
	public void drawFramerate() {
		m_iFrameCount++;
		if ((int)(applet.millis()/1000) > m_iLastSecond) {
			m_iLastSecond = ((int)(applet.millis()/1000));
			m_iFramerate = m_iFrameCount;
			//m_framerate.setText("" + m_iFrameCount);
			m_iFrameCount = 0;
			m_bDrawFramerate = true;
			m_fFramerateMultiplier = (float)m_iFramerate / (float)averageSuspectedFrameRate;
		}
		if (m_bDrawFramerate){
			if (m_bFrameRateDrawingOn) {
				System.out.println("" + m_iFramerate + " fps");
				m_vPos.printMe("m_vPos");
				m_vCenter.printMe("m_vCenter");
			}
			m_bDrawFramerate = false;
		}
	}

	public float getFramerateMultiplier(){
		return m_fFramerateMultiplier;
	}

	public void setControlsEnabled(Boolean status) {
		applet.controls.setEnabled(status.booleanValue());
	}

	public void resetBuildings() {
		applet.objectManager.resetBuildings();
	}

	//2D shapes drawing next to the camera avoiding clipping problems - deprecated! :)
	public void _rect(int _x, int _y, int _width, int _height) {
		/*beginShape(TRIANGLES);
		_vertex(get3DFrom2D(x				, y				 ));
		_vertex(get3DFrom2D(x + width, y				 ));
		_vertex(get3DFrom2D(x + width, y + height));
		_vertex(get3DFrom2D(x				, y				 ));
		_vertex(get3DFrom2D(x + width, y + height));
		_vertex(get3DFrom2D(x				, y + height));
		endShape();*/
		float x			= _x * m_fMousePlaneMultiplier;
		float y			= _y * m_fMousePlaneMultiplier;
		float width		= _width * m_fMousePlaneMultiplier;
		float height	= _height * m_fMousePlaneMultiplier;
		System.out.println(" " + " ");
		applet.pushMatrix();
		FVector translateTo = FVector.multiply(getLook(), -m_fDistanceEyeMousePlane + m_fNearClip);
		applet.translate(translateTo.x, translateTo.y, translateTo.z);
		//rect(x,y,width,height);
		applet.beginShape(PConstants.TRIANGLES);
		applet.vertex(x				, y			);
		applet.vertex(x + width		, y			);
		applet.vertex(x + width		, y + height);
		applet.vertex(x				, y			);
		applet.vertex(x + width		, y + height);
		applet.vertex(x				, y + height);
		applet.endShape();
		applet.popMatrix();
	}

	//setting a vertex with a FVector
	private void vertex(FVector vert) {
		applet.vertex((int)vert.x, (int)vert.y, (int)vert.z);
	}

}
