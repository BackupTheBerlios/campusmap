package CampusMap;

import processing.core.*;


/** Class describing a human that moves around on the groundplane
 *
 */

public class Human extends MovingObject{

	protected final static int MOVE_Z 			= 20;
	protected final static int FADE_DISTANCE	= 500; // distance at which the object fades out

	boolean interestedInMouse = false;

	//constructor
	public Human(CampusMap drawApplet, FVector position) {
		super(drawApplet, position);
		newRandomDirection();
		fullSpeed = myDrawApplet.random(10, 20);
		currentSpeed = fullSpeed;
		if (myDrawApplet.random(0,3) < 1.0f)
			interestedInMouse = true;

	}

	// draw method
	public void draw(){
		myDrawApplet.noStroke();
		myDrawApplet.pushMatrix();
		myDrawApplet.translate(myPos.getX(), myPos.getY(), myPos.getZ());
//		myDrawApplet.scale(myScale.getX(), myScale.getY(), myScale.getZ());
//		myDrawApplet.rotateX(myRot.getX());
//		myDrawApplet.rotateY(myRot.getY());
		myDrawApplet.rotateZ(myDrawApplet.atan2(myDir.y, myDir.x)+ PConstants.PI/2);
		//body
		myDrawApplet.fill(100, 100, 100, 255);
		myDrawApplet.translate(0, 0, 3);
		myDrawApplet.box(2,1,4.5f);
		//head
		myDrawApplet.fill(240, 180, 180, 255);
		myDrawApplet.translate(0, 0, 3);
		myDrawApplet.sphereDetail(3);
		myDrawApplet.sphere(1);
		myDrawApplet.popMatrix();
	}

	public void move(float partOfSecond, boolean doCheck) {
		myPos.addMe(FVector.multiply(myDir, currentSpeed*partOfSecond));
		if (doCheck) {
			if (myDrawApplet.groundPlane.check(myPos, 1.0f))
				myDir.multiplyMe(-1);
/*			else {
				CollisionSphere sphere = myDrawApplet.objectManager.testBuildingsWithPoint(myPos, 20.f);
				if (sphere != null)
					myDir = sphere.getTangent(myPos);
				else {
					if ((!myDrawApplet.isTouring() && myDrawApplet.objectsGoForTheMouse && interestedInMouse) || myDrawApplet.random(0,20) < 1.0f)
						newRandomDirection();
					if (!myDrawApplet.isTouring() && myDrawApplet.objectsGoForTheMouse && myDrawApplet.random(0,35) < 1.0f)
						interestedInMouse = !interestedInMouse;
				}
			}
*/		}
	}

	private void newRandomDirection() {
		if (!myDrawApplet.isTouring() && myDrawApplet.objectsGoForTheMouse && interestedInMouse)
		{
			myDir.set(myDrawApplet.theCamera.getMouseGroundPlaneIntersection(false));
			myDir.subtractMe(myPos);
		}
		else
		{
			myDir.x = myDrawApplet.random(-1, 1);
			myDir.y = myDrawApplet.random(-1, 1);
			myDir.z = 0;
		}
		if (!myDrawApplet.isTouring() && myDrawApplet.objectsGoForTheMouse && interestedInMouse && myDir.magnitudeSqr() < 225)
			myDir.set(0,0,0);
		else
			myDir.normalizeMe();
	}

}
