package CampusMap;

import java.util.Vector;
import processing.core.*;

/*
 * Created on 09.02.2006
 *
 * TODO To change the template for this generated file go to
 * Window - Preferences - Java - Code Style - Code Templates
 */

/**
 * @author David
 */
//class to manage al the moving objects like people, buses, etc.

class MovingObjectsManager {
	
	private static final int NUMBER_OF_PEOPLE = 25;
	private static final int CHECK_MS = 1000;
	
	private CampusMap applet;
	private Vector movingObjects;
	
	private int lastTime = 0;
	private int lastCheck = 0;
	private int lastMinute = 0;
	
	public MovingObjectsManager(CampusMap p_applet) {
		applet = p_applet;
		applet.env.objectInitDisplay.setText("MovingObjectsManager");
		movingObjects = new Vector();
		
		//create humans
		FVector[] positions = applet.groundPlane.getRandomPositionsOnGroundPlane(NUMBER_OF_PEOPLE, true, true, 20.0f, 0.5f, false);
		for (int i = 0; i < NUMBER_OF_PEOPLE; i++) {
			movingObjects.add(new Human(applet, positions[i]));
		}

		lastMinute = PApplet.minute()-1;
	}
	
	public void process() {
		float partOfSecond = (applet.millis() - lastTime) / 1000.0f;
		lastTime = applet.millis();
		boolean doCheck = false;
		if ((lastTime - lastCheck) > CHECK_MS) {
			lastCheck = lastTime;
			doCheck = true;
		}
		if (PApplet.minute() != lastMinute) {
			lastMinute = PApplet.minute();
			Bus.CREATE_BUSES_STARTING_AT(PApplet.hour(), lastMinute, movingObjects, applet);
		}
		for(int i = 0;i < movingObjects.size(); i++) {
			((MovingObject)movingObjects.elementAt(i)).move(partOfSecond, doCheck);
			if ( ((MovingObject)movingObjects.elementAt(i)).getRequestingMedicide() )
				movingObjects.removeElementAt(i);
		}
	}
	
	public void draw() {
		for(int i = 0;i < movingObjects.size(); i++) {
			((MovingObject)movingObjects.elementAt(i)).draw();
		}
	}
	
}