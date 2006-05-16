package CampusMap;

/** Class describing main funcionality of objects that move around on the groundplane 
 * 
 */

public class MovingObject{
	
	protected final static int MOVE_Z 			= 20;
	protected static CampusMap myDrawApplet;

	protected FVector myPos;
	protected FVector myRot;
	protected FVector myScale;
	protected FVector myDir;
	protected float sinusVal = 0.0f;
	protected float alpha = 1.0f;
	protected float fullSpeed = 10.0f;
	protected float currentSpeed = 10.0f;
	protected boolean requestingMedicide;
	
	//constructor
	public MovingObject(CampusMap drawApplet, FVector position) {
		myDrawApplet = drawApplet;
		myPos	= position;
		//myPos.setZ(myPos.getZ()+MOVE_Z);
		myRot	= new FVector(1, 0, 0);
		myScale	= new FVector(1, 1, 1);
		myDir = new FVector(1, 0, 0);
		requestingMedicide = false;
	}
	
	// draw method
	public void draw(){
		myDrawApplet.pushMatrix();
		myDrawApplet.translate(myPos.getX(), myPos.getY(), myPos.getZ());
		myDrawApplet.scale(myScale.getX(), myScale.getY(), myScale.getZ());
		myDrawApplet.rotateX(myRot.getX());
		myDrawApplet.rotateY(myRot.getY());
		myDrawApplet.rotateZ(myRot.getZ());
		myDrawApplet.noStroke();
		myDrawApplet.fill(240, 150, 150, 255);
		myDrawApplet.sphere(200);
		myDrawApplet.popMatrix();
	}
	
	public void move(float partOfSecond, boolean doCheck) {
		myPos.addMe(myDir.multiply(currentSpeed*partOfSecond));
	}
	
	public boolean getRequestingMedicide() {
		return requestingMedicide;
	}
	
}