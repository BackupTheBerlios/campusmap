package CampusMap;
import processing.core.PConstants;
import javax.swing.*;
import java.awt.event.*;

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


class Controls{
	static	final	int	DOUBLE_CLICK_SPEED		= 250;
	
	private CampusMap applet;
	
	private	float	timeStored;
	private	Camera	theCamera;
	private	boolean	controlsEnabled;
	private	boolean	tempControlDisable;
	private	boolean	threeDeeControlEnabled;	
	private boolean circleControlEnabled;
	
	public boolean mouseJustMoved;
	public boolean mouseJustPressed;
	public boolean mouseJustReleased;
	public boolean mousePressedLastState;
	public boolean mouseJustDragged;
	
	public boolean leftButtonPressed;
	public boolean rightButtonPressed;
	public boolean upButtonPressed;
	public boolean downButtonPressed;
	
	private int lastMouseX;
	private int lastMouseY;
	
	private ActionEvent posChange;
	private ActionEvent povChange;
	
	private MouseWheel mouseWheel;

	public Controls(CampusMap _applet, Camera theCamera) {
		applet					= _applet;
		applet.env.objectInitDisplay.setText("Controls");
		timeStored				= 0;
		this.theCamera			= theCamera;
		controlsEnabled			= false;  // longer
		tempControlDisable		= false; // just for THIS DRAW loop
		threeDeeControlEnabled	= true;
		
		circleControlEnabled	= false;
		
		mouseJustMoved			= false;
		mouseJustPressed		= false;
		mouseJustReleased		= false;
		mousePressedLastState	= false;
		mouseJustDragged		= false;
		
		leftButtonPressed		= false;
		rightButtonPressed		= false;
		upButtonPressed			= false;
		downButtonPressed		= false;
		
		posChange				= new ActionEvent(this, 0, "posChange");
		povChange				= new ActionEvent(this, 1, "povChange");
		
		lastMouseX				= applet.mouseX;
		lastMouseY				= applet.mouseY;
		
		mouseWheel				= new MouseWheel(applet, theCamera, this);
		
		/*applet.addMouseWheelListener(new java.awt.event.MouseWheelListener() {
		    public void mouseWheelMoved(java.awt.event.MouseWheelEvent evt) {
		    	if (controlsEnabled) {
		    		System.out.println("wheel " + (evt.getWheelRotation() * -10));
		    		theCamera.moveToInter(theCamera.getPos().add(new FVector(0,0,(evt.getWheelRotation() * -10))), new Integer (400), new Boolean(true) );
		    	}
		    }
		  }); */
	}

	public void setTempDisable(boolean onOff) {
		circleControlEnabled = onOff;
		if (circleControlEnabled) {
			tempControlDisable = true;
		}
		else {
			tempControlDisable = false;
		}
	}
	
	public void setEnabled(boolean onOff){
		controlsEnabled=onOff;
		threeDeeControlEnabled = onOff;
		applet.objectManager.guiObjects.setGlobalActive(onOff);
	}
	
	public boolean getEnabled(){
		return controlsEnabled;
	}

	public void setThreeDeeControlEnabled(boolean nAble){
		threeDeeControlEnabled = nAble;
	}
	
	public boolean getThreeDeeControlEnabled(){
		return threeDeeControlEnabled;
	}
	
	public void reset() {
		if (GuiObject.getIsInUse()){ // || applet.buttons.getIsInUse()) {
			 tempControlDisable = true;
			//System.out.println("controls disabled");
		}
		else tempControlDisable = false;
		mouseJustMoved			= false;
		mouseJustPressed		= false;
		mouseJustReleased		= false;
	}


	public void mouseDragged() {
		if (controlsEnabled && !tempControlDisable && threeDeeControlEnabled) {
			
			//activate this thing
			/*if (controls.mouseJustPressed) {
				//this should now only be enabled, when no other control objects like buttons and sliders are used at this very moment
				//if ( ((mouseX - 50) * (mouseX - 50) + (mouseY - 315) * (mouseY - 315)) < 900) {
					rotOn		= true;
					lastMouseX	= mouseX;
					lastMouseY	= mouseY;
				//}
			}
			
			//turn it off
			else if (controls.mouseJustReleased) {
				rotOn	= false;
			}*/
			
			//calculate the rotation
			//else if (rotOn) {
			float tempRotX = (float)Math.toRadians((applet.mouseX - lastMouseX) * 0.33f);
			float tempRotY = (float)Math.toRadians((applet.mouseY - lastMouseY) * 0.33f);
			theCamera.yaw(-tempRotX);	//minus for "grabbing" screen and pulling camera
			theCamera.pitch(-tempRotY);	//minus for "grabbing" screen and pulling camera
			applet.letterRotX	+= tempRotX;
			applet.letterRotY 	+= tempRotY;
			lastMouseX = applet.mouseX;
			lastMouseY = applet.mouseY;
			//}
			
			mouseJustDragged=true;
		}
	}

	public void mouseMoved() {
		mouseJustMoved=true;
		if (circleControlEnabled) {
			theCamera.rotateToNow(new Float((float)Math.toRadians((applet.mouseX - lastMouseX) * 0.5f)), new Float(0.0f));
			lastMouseX = applet.mouseX;
			//lastMouseY = applet.mouseY;
			
		}
	}

	public void mouseMovedOverObject(){
		System.out.println("Objekt überfahren!");
	}

	public void mousePressed() {
		mouseJustPressed = true;
		if (controlsEnabled && !tempControlDisable && threeDeeControlEnabled) {
			lastMouseX	= applet.mouseX;
			lastMouseY	= applet.mouseY;
		}
	}

	public void mouseReleased() {
		mouseJustReleased = true;
		if (controlsEnabled && !tempControlDisable && threeDeeControlEnabled) {
			if (applet.millis() - timeStored < DOUBLE_CLICK_SPEED) {
				// DOUBLE CLICK
				doubleClick();
				timeStored = 0;
				// Safe movement invoke
			}
			else {
				// JUST ONE Click
				timeStored = applet.millis();
			}
		}
	}
	
	public void doubleClick() {
		FVector newTarget = theCamera.getMouseGroundPlaneIntersection(true);
		if (!newTarget.isZero()) {
			
			//test circle view fly into
			//theCamera.flyIntoCircleView(newTarget, 200, 45.0f, 2000);
			//Object[] actionObjects = {new Float(5.0f), new Float(20.0f), new Integer(3000), new Boolean(true)};
			//theCamera.queueAction("rotateToInter", 2100, actionObjects);
			
			theCamera.birdView(newTarget, 60.0f, 2000, 0.0f);
			//theCamera.moveToInter(theCamera.birdView(newTarget, 0.0f), 2000, true);
			}
	}
	
	public void keyPressed() {
		if (controlsEnabled && !tempControlDisable) {
			if (applet.keyCode == PConstants.LEFT || applet.key == 'A' || applet.key == 'a') {
				leftButtonPressed	= true;
			}
			else if (applet.keyCode == PConstants.RIGHT || applet.key == 'D' || applet.key == 'd') {
				rightButtonPressed	= true;
			}
			else if (applet.keyCode == PConstants.UP || applet.key == 'W' || applet.key == 'w') {
				upButtonPressed		= true;
			}
			else if (applet.keyCode == PConstants.DOWN || applet.key == 'S' || applet.key == 's') {
				downButtonPressed	= true;
			}
			else if (applet.keyCode == PConstants.ENTER) {
				theCamera.birdView(new FVector(0,200,200), 45.0f, 2000, 0.0f);
			}
			else if (Environment.adminMode) {
				if (applet.key == 'L' || applet.key == 'l') {
					applet.locator.activate();
				}
				if (applet.key == 'C' || applet.key == 'c') {
					applet.drawDebugSpheres = !applet.drawDebugSpheres;
				}
				if (applet.key == 'K' || applet.key == 'k') {
					circleControlEnabled = !circleControlEnabled;
				}
				if (applet.key == 'F' || applet.key == 'f') {
					theCamera.m_bFrameRateDrawingOn = !theCamera.m_bFrameRateDrawingOn;
				}
				if (applet.key == 'M' || applet.key == 'm') {
					applet.objectsGoForTheMouse = !applet.objectsGoForTheMouse;
				}
			}
		}
	}
	
	
	public void keyReleased() {
		if (applet.keyCode == PConstants.LEFT || applet.key == 'A' || applet.key == 'a') {
			leftButtonPressed	= false;
		}
		else if (applet.keyCode == PConstants.RIGHT || applet.key == 'D' || applet.key == 'd') {
			rightButtonPressed	= false;
		}
		else if (applet.keyCode == PConstants.UP || applet.key == 'W' || applet.key == 'w') {
			upButtonPressed		= false;
		}
		else if (applet.keyCode == PConstants.DOWN || applet.key == 'S' || applet.key == 's') {
			downButtonPressed	= false;
		}
		else if (controlsEnabled && !tempControlDisable) {
			if (applet.keyCode == KeyEvent.VK_PAGE_UP && theCamera.getCenterInAction().isZero()){
				theCamera.moveCameraVertically(-1);
			}
			else if (applet.keyCode == KeyEvent.VK_PAGE_DOWN && theCamera.getCenterInAction().isZero()){
				theCamera.moveCameraVertically(1);
			}
		}
	}
	
	public void pageUpPressed() {
		if (controlsEnabled && theCamera.getCenterInAction().isZero()){
			theCamera.moveCameraVertically(-1);
		}
	}

	public void pageDownPressed() {
		if (controlsEnabled && theCamera.getCenterInAction().isZero()){
			theCamera.moveCameraVertically(1);
		}
	}	
	
}//end of class controls

class MouseWheel implements java.awt.event.MouseWheelListener {

	Camera theCamera;
	Controls controls;
	
	public MouseWheel(java.awt.Component comp, Camera _theCamera, Controls _controls) {
		theCamera	= _theCamera;
		controls	= _controls;
		comp.addMouseWheelListener(this);
	}

	public void mouseWheelMoved(java.awt.event.MouseWheelEvent evt) {
		//controls should be disabled when flying around!
		if (controls.getEnabled() && theCamera.getCenterInAction().isZero()) {
			theCamera.moveCameraVertically(evt.getWheelRotation());			
		}
	}
} 